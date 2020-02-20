<?php
declare(strict_types=1);

namespace Sirius\Orm;

use Sirius\Orm\Action\BaseAction;
use Sirius\Orm\Action\Delete;
use Sirius\Orm\Action\Insert;
use Sirius\Orm\Action\Update;
use Sirius\Orm\Behaviours\BehaviourInterface;
use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Collection\PaginatedCollection;
use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\Entity\GenericEntity;
use Sirius\Orm\Entity\GenericEntityHydrator;
use Sirius\Orm\Entity\HydratorInterface;
use Sirius\Orm\Entity\StateEnum;
use Sirius\Orm\Entity\Tracker;
use Sirius\Orm\Helpers\Arr;
use Sirius\Orm\Helpers\Inflector;
use Sirius\Orm\Helpers\QueryHelper;
use Sirius\Orm\Relation\Relation;

/**
 * @method array where($column, $value, $condition)
 * @method array columns(string $expr, string ...$exprs)
 * @method array orderBy(string $expr, string ...$exprs)
 */
class Mapper
{
    /**
     * Name of the class/interface to be used to determine
     * if this mapper can persist a specific entity
     * @var string
     */
    protected $entityClass = GenericEntity::class;

    /**
     * @var string|array
     */
    protected $primaryKey = 'id';

    /**
     * @var string
     */
    protected $table;

    /**
     * Used in queries like so: FROM table as tableAlias
     * This is especially useful if you are using prefixed tables
     * @var string
     */
    protected $tableAlias = '';

    /**
     * @var string
     */
    protected $tableReference;

    /**
     * Table columns
     * @var array
     */
    protected $columns = [];

    /**
     * Column aliases (table column => entity attribute)
     * @var array
     */
    protected $columnAttributeMap = [];

    /**
     * @var HydratorInterface
     */
    protected $entityHydrator;

    /**
     * Default attributes
     * @var array
     */
    protected $entityDefaultAttributes = [];

    /**
     * List of behaviours to be attached to the mapper
     * @var array[BehaviourInterface]
     */
    protected $behaviours = [];

    /**
     * @var array
     */
    protected $relations = [];

    /**
     * @var array
     */
    protected $scopes = [];

    /**
     * @var array
     */
    protected $guards = [];

    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var Orm
     */
    protected $orm;

    /**
     * @var Query
     */
    private $queryPrototype;

    public static function make(Orm $orm, MapperConfig $mapperConfig)
    {
        $mapper                          = new static($orm, $mapperConfig->entityHydrator);
        $mapper->table                   = $mapperConfig->table;
        $mapper->tableAlias              = $mapperConfig->tableAlias;
        $mapper->primaryKey              = $mapperConfig->primaryKey;
        $mapper->columns                 = $mapperConfig->columns;
        $mapper->entityDefaultAttributes = $mapperConfig->entityDefaultAttributes;
        $mapper->columnAttributeMap      = $mapperConfig->columnAttributeMap;
        $mapper->scopes                  = $mapperConfig->scopes;
        $mapper->guards                  = $mapperConfig->guards;
        $mapper->tableReference          = QueryHelper::reference($mapper->table, $mapper->tableAlias);

        if ($mapperConfig->relations) {
            $mapper->relations = array_merge($mapper->relations, $mapperConfig->relations);
        }

        if ($mapperConfig->entityClass) {
            $mapper->entityClass = $mapperConfig->entityClass;
        }

        if ($mapperConfig->behaviours && ! empty($mapperConfig->behaviours)) {
            $mapper->use(...$mapperConfig->behaviours);
        }

        return $mapper;
    }

    public function __construct(Orm $orm, HydratorInterface $entityHydrator = null, QueryBuilder $queryBuilder = null)
    {
        $this->orm = $orm;
        if (! $entityHydrator) {
            $entityHydrator = new GenericEntityHydrator($orm, $this);
        }
        if (! $queryBuilder) {
            $this->queryBuilder = QueryBuilder::getInstance();
        }
        $this->entityHydrator = $entityHydrator;
        $this->tableReference = QueryHelper::reference($this->table, $this->tableAlias);
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


        throw new \BadMethodCallException('Unknown method {$method} for class ' . get_class($this));
    }

    /**
     * Add behaviours to the mapper
     *
     * @param mixed ...$behaviours
     */
    public function use(...$behaviours)
    {
        if (empty($behaviours)) {
            return;
        }
        foreach ($behaviours as $behaviour) {
            /** @var $behaviour BehaviourInterface */
            if (isset($this->behaviours[$behaviour->getName()])) {
                throw new \BadMethodCallException(
                    sprintf('Behaviour "%s" is already registered', $behaviour->getName())
                );
            }
            $this->behaviours[$behaviour->getName()] = $behaviour;
        }
    }

    public function without(...$behaviours)
    {
        if (empty($behaviours)) {
            return $this;
        }
        $mapper = clone $this;
        foreach ($behaviours as $behaviour) {
            unset($mapper->behaviours[$behaviour]);
        }

        return $mapper;
    }

    public function addQueryScope($scope, callable $callback)
    {
        $this->scopes[$scope] = $callback;
    }

    public function getQueryScope($scope)
    {
        return $this->scopes[$scope] ?? null;
    }

    public function registerCasts(CastingManager $castingManager)
    {
        $mapper = $this;

        $singular = Inflector::singularize($this->getTableAlias(true));
        $castingManager->register($singular, function ($value) use ($mapper, $castingManager) {
            if ($value instanceof $this->entityClass) {
                return $value;
            }

            return $value !== null ? $mapper->newEntity($value, $castingManager) : null;
        });

        $plural = $this->getTableAlias(true);
        $castingManager->register($plural, function ($values) use ($mapper, $castingManager) {
            if ($values instanceof Collection) {
                return $values;
            }
            $collection = new Collection();
            foreach ($values as $value) {
                $collection->add($mapper->newEntity($value, $castingManager));
            }

            return $collection;
        });
    }

    /**
     * @return array|string
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getTableAlias($returnTableIfNull = false)
    {
        return (! $this->tableAlias && $returnTableIfNull) ? $this->table : $this->tableAlias;
    }

    public function getTableReference()
    {
        return $this->tableReference;
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return array
     */
    public function getColumnAttributeMap(): array
    {
        return $this->columnAttributeMap;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @return array
     */
    public function getGuards(): array
    {
        return $this->guards;
    }

    /**
     * @param $data
     *
     * @return EntityInterface
     */
    public function newEntity(array $data): EntityInterface
    {
        $entity = $this->entityHydrator->hydrate(array_merge($this->getEntityDefaults(), $data));

        return $this->applyBehaviours(__FUNCTION__, $entity);
    }

    public function extractFromEntity(EntityInterface $entity): array
    {
        $data = $this->entityHydrator->extract($entity);

        return $this->applyBehaviours(__FUNCTION__, $data);
    }

    public function newEntityFromRow(array $data = null, array $load = [], Tracker $tracker = null)
    {
        if ($data == null) {
            return null;
        }

        $receivedTracker = ! ! $tracker;
        if (! $tracker) {
            $receivedTracker = false;
            $tracker         = new Tracker($this, [$data]);
        }

        $entity = $this->newEntity($data);
        $this->injectRelations($entity, $tracker, $load);
        $entity->setPersistenceState(StateEnum::SYNCHRONIZED);

        if (! $receivedTracker) {
            $tracker->replaceRows([$entity]);
            if ($tracker->isDisposable()) {
                unset($tracker);
            }
        }

        return $entity;
    }

    public function newCollectionFromRows(array $rows, array $load = []): Collection
    {
        $entities = [];
        $tracker  = new Tracker($this, $rows);
        foreach ($rows as $row) {
            $entity     = $this->newEntityFromRow($row, $load, $tracker);
            $entities[] = $entity;
        }
        $tracker->replaceRows($entities);
        if ($tracker->isDisposable()) {
            unset($tracker);
        }

        return new Collection($entities);
    }

    public function newPaginatedCollectionFromRows(
        array $rows,
        int $totalCount,
        int $perPage,
        int $currentPage,
        array $load = []
    ): PaginatedCollection {
        $entities = [];
        $tracker  = new Tracker($this, $rows);
        foreach ($rows as $row) {
            $entity     = $this->newEntityFromRow($row, $load, $tracker);
            $entities[] = $entity;
        }
        $tracker->replaceRows($entities);
        if ($tracker->isDisposable()) {
            unset($tracker);
        }

        return new PaginatedCollection($entities, $totalCount, $perPage, $currentPage);
    }

    protected function injectRelations(EntityInterface $entity, Tracker $tracker, array $eagerLoad = [])
    {
        $trackerIdDisposable = true;
        foreach (array_keys($this->relations) as $name) {
            $relation      = $this->getRelation($name);
            $queryCallback = $eagerLoad[$name] ?? null;
            $nextLoad      = Arr::getChildren($eagerLoad, $name);

            if (! $tracker->hasRelation($name)) {
                $tracker->setRelation($name, $relation, $queryCallback);
            }

            if (array_key_exists($name, $eagerLoad) || $relation->isEagerLoad()) {
                $relation->attachMatchesToEntity($entity, $tracker->getRelationResults($name));
            } elseif ($relation->isLazyLoad()) {
                $trackerIdDisposable = false;
                $relation->attachLazyValueToEntity($entity, $tracker);
            }
        }

        $tracker->setDisposable($trackerIdDisposable);
    }

    protected function getEntityDefaults()
    {
        return $this->entityDefaultAttributes;
    }

    public function setEntityAttribute(EntityInterface $entity, $attribute, $value)
    {
        return $entity->set($attribute, $value);
    }

    public function getEntityAttribute(EntityInterface $entity, $attribute)
    {
        return $entity->get($attribute);
    }

    public function hasRelation($name): bool
    {
        return isset($this->relations[$name]);
    }

    public function getRelation($name): Relation
    {
        if (! $this->hasRelation($name)) {
            throw new \InvalidArgumentException("Relation named {$name} is not registered for this mapper");
        }

        if (is_array($this->relations[$name])) {
            $this->relations[$name] = $this->orm->createRelation($this, $name, $this->relations[$name]);
        }
        $relation = $this->relations[$name];
        if (! $relation instanceof Relation) {
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
        $query = $this->queryBuilder->newQuery($this);

        return $this->applyBehaviours(__FUNCTION__, $query);
    }

    public function find($pk, array $load = [])
    {
        return $this->newQuery()
                    ->where($this->getPrimaryKey(), $pk)
                    ->load(...$load)
                    ->first();
    }

    /**
     * @param EntityInterface $entity
     *
     * @return bool
     * @throws \Exception
     */
    public function save(EntityInterface $entity, $withRelations = true)
    {
        $this->assertCanPersistEntity($entity);
        $action = $this->newSaveAction($entity, ['relations' => $withRelations]);

        $this->orm->getConnectionLocator()->lockToWrite(true);
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

    public function newSaveAction(EntityInterface $entity, $options): BaseAction
    {
        if (! $entity->getPk()) {
            $action = new Insert($this, $entity, $options);
        } else {
            $action = new Update($this, $entity, $options);
        }

        return $this->applyBehaviours('save', $action);
    }

    public function delete(EntityInterface $entity, $withRelations = true)
    {
        $this->assertCanPersistEntity($entity);

        $action = $this->newDeleteAction($entity, ['relations' => $withRelations]);

        $this->orm->getConnectionLocator()->lockToWrite(true);
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
        $action = new Delete($this, $entity, $options);

        return $this->applyBehaviours('delete', $action);
    }

    protected function assertCanPersistEntity($entity)
    {
        if (! $entity || ! $entity instanceof $this->entityClass) {
            throw new \InvalidArgumentException(sprintf(
                'Mapper %s can only persist entity of class %s. %s class provided',
                __CLASS__,
                $this->entityClass,
                get_class($entity)
            ));
        }
    }

    protected function applyBehaviours($target, $result, ...$args)
    {
        foreach ($this->behaviours as $behaviour) {
            $method = 'on' . Helpers\Str::className($target);
            if (method_exists($behaviour, $method)) {
                $result = $behaviour->{$method}($this, $result, ...$args);
            }
        }

        return $result;
    }

    public function getReadConnection()
    {
        return $this->orm->getConnectionLocator()->getRead();
    }

    public function getWriteConnection()
    {
        return $this->orm->getConnectionLocator()->getWrite();
    }
}
