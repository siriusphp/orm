<?php
declare(strict_types=1);

namespace Sirius\Orm;

use Sirius\Orm\Action\Delete;
use Sirius\Orm\Action\Insert;
use Sirius\Orm\Action\Update;
use Sirius\Orm\Behaviour\BehaviourInterface;
use Sirius\Orm\Contract\EntityInterface;
use Sirius\Orm\Contract\HydratorInterface;
use Sirius\Orm\Entity\GenericHydrator;
use Sirius\Orm\Exception\FailedActionException;
use Sirius\Orm\Relation\Relation;

/**
 * @method Query where($column, $value, $condition)
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
     * @var HydratorInterface
     */
    protected $hydrator;

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

        $mapper->relations = $mapperConfig->getRelations();

        $mapper->hydrator->setMapperConfig($mapperConfig);

        return $mapper;
    }

    public function __construct(ConnectionLocator $connectionLocator)
    {
        $this->connectionLocator = $connectionLocator;
        $this->behaviours        = new Behaviours();
        $this->init();
    }

    protected function init() {
        $this->hydrator          = new GenericHydrator();
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

    public function setOrm(Orm $orm)
    {
        $this->orm = $orm;
        $this->hydrator->setCastingManager($this->orm->getCastingManager());
    }

    /**
     * @return MapperConfig
     */
    public function getConfig(): MapperConfig
    {
        return $this->mapperConfig;
    }

    /**
     * @return HydratorInterface
     */
    public function getHydrator(): HydratorInterface
    {
        return $this->hydrator;
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
        $entity = $this->getHydrator()
                       ->hydrate(array_merge(
                           $this->getConfig()->getDefaultEntityAttributes(),
                           $data
                       ));

        return $this->behaviours->apply($this, __FUNCTION__, $entity);
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

    /**
     * @return Query
     */
    public function newQuery()
    {
        $query = new Query($this->getReadConnection(), $this);

        return $this->behaviours->apply($this, __FUNCTION__, $query);
    }

    public function getReadConnection(): Connection
    {
        return $this->connectionLocator->getRead();
    }

    public function getWriteConnection(): Connection
    {
        return $this->connectionLocator->getWrite();
    }
}
