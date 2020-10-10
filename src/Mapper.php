<?php
declare(strict_types=1);

namespace Sirius\Orm;

use Sirius\Orm\Action\Delete;
use Sirius\Orm\Action\Insert;
use Sirius\Orm\Action\Update;
use Sirius\Orm\Behaviour\BehaviourInterface;
use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\Exception\FailedActionException;
use Sirius\Orm\Relation\Relation;

/**
 * @method Query where($column, $value, $condition)
 * @method Query columns(string $expr, string ...$exprs)
 * @method Query orderBy(string $expr, string ...$exprs)
 */
class Mapper
{
    /**
     * @var Orm
     */
    protected $orm;

    /**
     * @var ConnectionLocator
     */
    protected $connectionLocator;

    /**
     * @var MapperConfig
     */
    protected $mapperConfig;

    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var Behaviours
     */
    protected $behaviours;

    /**
     * @var array
     */
    protected $relations = [];

    public static function make(ConnectionLocator $connectionLocator, MapperConfig $mapperConfig)
    {
        $mapper               = new static($connectionLocator);
        $mapper->mapperConfig = $mapperConfig;

        if ( ! empty($mapperConfig->getBehaviours())) {
            $mapper->use(...$mapperConfig->getBehaviours());
        }

        return $mapper;
    }

    public function __construct(ConnectionLocator $connectionLocator)
    {
        $this->connectionLocator = $connectionLocator;
        $this->queryBuilder      = QueryBuilder::getInstance();
        $this->behaviours        = new Behaviours();
    }

    public function setOrm(Orm $orm)
    {
        $this->orm = $orm;
    }

    public function __call(string $method, array $params)
    {
        switch ($method) {
            case 'where':
            case 'columns':
            case 'orderBy':
                $query = $this->newQuery();

                return $query->{$method}(...$params);
        }

        throw new \BadMethodCallException("Unknown method {$method} for class " . get_class($this));
    }

    /**
     * @return MapperConfig
     */
    public function getConfig(): MapperConfig
    {
        return $this->mapperConfig;
    }

    /**
     * Add behaviours to the mapper
     *
     * @param mixed ...$behaviours
     */
    public function use(...$behaviours)
    {
        /** @var BehaviourInterface $behaviour */
        foreach ($behaviours as $behaviour) {
            $this->behaviours->add($behaviour);
        }
    }

    /**
     * Create a clone of the mapper without the selected behaviour
     *
     * @param mixed ...$behaviours
     *
     * @return self
     */
    public function without(...$behaviours)
    {
        $mapper             = clone $this;
        $mapper->behaviours = $this->behaviours->without(...$behaviours);

        return $mapper;
    }

    public function addQueryScope($scope, callable $callback)
    {
        $this->mapperConfig->addQueryScope($scope, $callback);
    }

    /**
     * @param $data
     *
     * @return EntityInterface
     */
    public function newEntity(array $data): EntityInterface
    {
        $entity = $this->getConfig()
                       ->getEntityHydrator()
                       ->hydrate(array_merge(
                           $this->getConfig()->getDefaultEntityAttributes(),
                           $data
                       ));

        return $this->behaviours->apply($this, __FUNCTION__, $entity);
    }

    public function extractFromEntity(EntityInterface $entity): array
    {
        $data = $this->getConfig()->getEntityHydrator()->extract($entity);

        return $this->behaviours->apply($this, __FUNCTION__, $data);
    }

    public function addRelation($name, $relation)
    {
        if (is_array($relation) || $relation instanceof Relation) {
            $this->relations[$name] = $relation;

            return;
        }
        throw new \InvalidArgumentException(
            sprintf('The relation has to be a Relation instance or an array of configuration options')
        );
    }

    public function hasRelation($name): bool
    {
        return isset($this->relations[$name]);
    }

    public function getRelation($name): Relation
    {
        if ( ! $this->hasRelation($name)) {
            throw new \InvalidArgumentException("Relation named {$name} is not registered for this mapper");
        }

        if (is_array($this->relations[$name])) {
            $this->relations[$name] = $this->orm->createRelation($this, $name, $this->relations[$name]);
        }
        $relation = $this->relations[$name];
        if ( ! $relation instanceof Relation) {
            throw new \InvalidArgumentException("Relation named {$name} is not a proper Relation instance");
        }

        return $relation;
    }

    public function getRelations(): array
    {
        return array_keys($this->relations);
    }

    public function newQuery(): Query
    {
        $query = $this->queryBuilder->newQuery($this->getReadConnection(), $this);

        return $this->behaviours->apply($this, __FUNCTION__, $query);
    }

    /**
     * @param mixed $pk Value of the primary key
     * @param array $load Eager load relations
     *
     * @return EntityInterface|null
     */
    public function find($pk, array $load = [])
    {
        return $this->newQuery()
                    ->where($this->getConfig()->getPrimaryKey(), $pk)
                    ->load(...$load)
                    ->first();
    }

    /**
     * @param EntityInterface $entity
     * @param bool|array $withRelations relations to be also updated
     *
     * @return bool
     * @throws FailedActionException
     */
    public function save(EntityInterface $entity, $withRelations = true)
    {
        $this->assertCanPersistEntity($entity);
        $action = $this->newSaveAction($entity, ['relations' => $withRelations]);

        $this->connectionLocator->lockToWrite(true);
        $this->getWriteConnection()->beginTransaction();
        try {
            $action->run();
            $this->getWriteConnection()->commit();
            $this->connectionLocator->lockToWrite(false);

            return true;
        } catch (FailedActionException $e) {
            $this->getWriteConnection()->rollBack();
            $this->connectionLocator->lockToWrite(false);
            throw $e;
        }
    }

    public function newSaveAction(EntityInterface $entity, $options): Update
    {
        if ( ! $this->getConfig()->getEntityHydrator()->getPk($entity)) {
            $action = new Insert($this->getWriteConnection(), $this, $entity, $options);
        } else {
            $action = new Update($this->getWriteConnection(), $this, $entity, $options);
        }

        return $this->behaviours->apply($this, __FUNCTION__, $action);
    }

    public function delete(EntityInterface $entity, $withRelations = false)
    {
        $this->assertCanPersistEntity($entity);

        $action = $this->newDeleteAction($entity, ['relations' => $withRelations]);

        $this->connectionLocator->lockToWrite(true);
        $this->getWriteConnection()->beginTransaction();
        try {
            $action->run();
            $this->getWriteConnection()->commit();

            return true;
        } catch (\Exception $e) {
            $this->getWriteConnection()->rollBack();
            throw $e;
        }
    }

    public function newDeleteAction(EntityInterface $entity, $options)
    {
        $action = new Delete($this->getWriteConnection(), $this, $entity, $options);

        return $this->behaviours->apply($this, __FUNCTION__, $action);
    }

    protected function assertCanPersistEntity($entity)
    {
        $entityClass = $this->mapperConfig->getEntityClass();
        if ( ! $entity || ! $entity instanceof $entityClass) {
            throw new \InvalidArgumentException(sprintf(
                'Mapper %s can only persist entity of class %s. %s class provided',
                __CLASS__,
                $entityClass,
                get_class($entity)
            ));
        }
    }

    protected function getReadConnection(): Connection
    {
        return $this->connectionLocator->getRead();
    }

    protected function getWriteConnection(): Connection
    {
        return $this->connectionLocator->getWrite();
    }
}
