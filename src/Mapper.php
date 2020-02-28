<?php
declare(strict_types=1);

namespace Sirius\Orm;

use Sirius\Orm\Action\BaseAction;
use Sirius\Orm\Action\Delete;
use Sirius\Orm\Action\Insert;
use Sirius\Orm\Action\Update;
use Sirius\Orm\Behaviour\BehaviourInterface;
use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Collection\PaginatedCollection;
use Sirius\Orm\Entity\Behaviours;
use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\Entity\GenericEntity;
use Sirius\Orm\Entity\GenericEntityHydrator;
use Sirius\Orm\Entity\HydratorInterface;
use Sirius\Orm\Entity\StateEnum;
use Sirius\Orm\Entity\Tracker;
use Sirius\Orm\Helpers\Arr;
use Sirius\Orm\Helpers\Inflector;
use Sirius\Orm\Helpers\QueryHelper;
use Sirius\Orm\Relation\Aggregate;
use Sirius\Orm\Relation\Relation;
use Sirius\Orm\Relation\RelationConfig;

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
     * Column casts
     * @var array
     */
    protected $casts = ['id' => 'int'];

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
     * @var Behaviours
     */
    protected $behaviours;

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

        if (isset($mapperConfig->casts) && !empty($mapperConfig->casts)) {
            $mapper->casts = $mapperConfig->casts;
        }

        if (!empty($mapperConfig->relations)) {
            $mapper->relations = array_merge($mapper->relations, $mapperConfig->relations);
        }

        if ($mapperConfig->entityClass) {
            $mapper->entityClass = $mapperConfig->entityClass;
        }

        if (isset($mapperConfig->behaviours) && ! empty($mapperConfig->behaviours)) {
            $mapper->use(...$mapperConfig->behaviours);
        }

        return $mapper;
    }

    public function __construct(Orm $orm, HydratorInterface $entityHydrator = null, QueryBuilder $queryBuilder = null)
    {
        $this->orm = $orm;

        if (! $entityHydrator) {
            $entityHydrator = new GenericEntityHydrator();
            $entityHydrator->setMapper($this);
            $entityHydrator->setCastingManager($orm->getCastingManager());
        }
        $this->entityHydrator = $entityHydrator;

        if (! $queryBuilder) {
            $queryBuilder = QueryBuilder::getInstance();
        }
        $this->queryBuilder = $queryBuilder;
        $this->behaviours = new Behaviours();
    }

    public function __call(string $method, array $params)
    {
        switch ($method) {
            case 'where':
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
        foreach ($behaviours as $behaviour) {
            $this->behaviours->add($behaviour);
        }
    }

    public function without(...$behaviours)
    {
        $mapper = clone $this;
        $mapper->behaviours = $this->behaviours->without(...$behaviours);

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
        $castingManager->register($singular, function ($value) use ($mapper) {
            if ($value instanceof $this->entityClass) {
                return $value;
            }

            return $value !== null ? $mapper->newEntity($value) : null;
        });

        $plural = $this->getTableAlias(true);
        $castingManager->register($plural, function ($values) use ($mapper) {
            if ($values instanceof Collection) {
                return $values;
            }
            $collection = new Collection();
            if (is_array($values)) {
                foreach ($values as $value) {
                    $collection->add($mapper->newEntity($value));
                }
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
        if (!$this->tableReference) {
            $this->tableReference = QueryHelper::reference($this->table, $this->tableAlias);
        }

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

        return $this->behaviours->apply($this, __FUNCTION__, $entity);
    }

    public function extractFromEntity(EntityInterface $entity): array
    {
        $data = $this->entityHydrator->extract($entity);

        return $this->behaviours->apply($this, __FUNCTION__, $data);
    }

    public function newEntityFromRow(array $data = null, array $load = [], Tracker $tracker = null)
    {
        if ($data == null) {
            return null;
        }

        $receivedTracker = ! ! $tracker;
        if (! $tracker) {
            $receivedTracker = false;
            $tracker         = new Tracker([$data]);
        }

        $entity = $this->newEntity($data);
        $this->injectRelations($entity, $tracker, $load);
        $this->injectAggregates($entity, $tracker, $load);
        $entity->setPersistenceState(StateEnum::SYNCHRONIZED);

        if (! $receivedTracker) {
            $tracker->replaceRows([$entity]);
        }

        return $entity;
    }

    public function newCollectionFromRows(array $rows, array $load = []): Collection
    {
        $entities = [];
        $tracker  = new Tracker($rows);
        foreach ($rows as $row) {
            $entity     = $this->newEntityFromRow($row, $load, $tracker);
            $entities[] = $entity;
        }
        $tracker->replaceRows($entities);

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
        $tracker  = new Tracker($rows);
        foreach ($rows as $row) {
            $entity     = $this->newEntityFromRow($row, $load, $tracker);
            $entities[] = $entity;
        }
        $tracker->replaceRows($entities);

        return new PaginatedCollection($entities, $totalCount, $perPage, $currentPage);
    }

    protected function injectRelations(EntityInterface $entity, Tracker $tracker, array $eagerLoad = [])
    {
        foreach (array_keys($this->relations) as $name) {
            $relation      = $this->getRelation($name);
            $queryCallback = $eagerLoad[$name] ?? null;
            $nextLoad      = Arr::getChildren($eagerLoad, $name);

            if (! $tracker->hasRelation($name)) {
                $tracker->setRelation($name, $relation, $queryCallback, $nextLoad);
            }

            if (array_key_exists($name, $eagerLoad) || in_array($name, $eagerLoad) || $relation->isEagerLoad()) {
                $relation->attachMatchesToEntity($entity, $tracker->getResultsForRelation($name));
            } elseif ($relation->isLazyLoad()) {
                $relation->attachLazyRelationToEntity($entity, $tracker);
            }
        }
    }

    protected function injectAggregates(EntityInterface $entity, Tracker $tracker, array $eagerLoad = [])
    {
        foreach (array_keys($this->relations) as $name) {
            $relation      = $this->getRelation($name);
            if (!method_exists($relation, 'getAggregates')) {
                continue;
            }
            $aggregates = $relation->getAggregates();
            foreach ($aggregates as $aggName => $aggregate) {
                /** @var $aggregate Aggregate */
                if (array_key_exists($aggName, $eagerLoad) || $aggregate->isEagerLoad()) {
                    $aggregate->attachAggregateToEntity($entity, $tracker->getAggregateResults($aggregate));
                } elseif ($aggregate->isLazyLoad()) {
                    $aggregate->attachLazyAggregateToEntity($entity, $tracker);
                }
            }
        }
    }

    protected function getEntityDefaults()
    {
        return $this->entityDefaultAttributes;
    }

    public function setEntityAttribute(EntityInterface $entity, $attribute, $value)
    {
        return $this->entityHydrator->set($entity, $attribute, $value);
    }

    public function getEntityAttribute(EntityInterface $entity, $attribute)
    {
        return $this->entityHydrator->get($entity, $attribute);
    }

    public function setEntityPk(EntityInterface $entity, $value)
    {
        if (is_array($this->primaryKey)) {
            foreach ($this->primaryKey as $k => $col) {
                $this->entityHydrator->set($entity, $col, $value[$k]);
            }
        }
        return $this->entityHydrator->set($entity, $this->primaryKey, $value);
    }

    public function getEntityPk(EntityInterface $entity)
    {
        if (is_array($this->primaryKey)) {
            $result = [];
            foreach ($this->primaryKey as $col) {
                $result[] = $this->entityHydrator->get($entity, $col);
            }

            return $result;
        }
        return $this->entityHydrator->get($entity, $this->primaryKey);
    }

    public function addRelation($name, $relation)
    {
        if (is_array($relation) || $relation instanceof Relation) {
            $this->relations[$name] = $relation;
            return;
        }
        throw new \InvalidArgumentException(
            sprintf('The relation has to be an Relation instance or an array of configuration options')
        );
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

        return $this->behaviours->apply($this, __FUNCTION__, $query);
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
            $this->orm->getConnectionLocator()->lockToWrite(false);
            return true;
        } catch (\Exception $e) {
            $this->getWriteConnection()->rollBack();
            $this->orm->getConnectionLocator()->lockToWrite(false);
            throw $e;
        }
    }

    public function newSaveAction(EntityInterface $entity, $options): Update
    {
        if (! $this->getEntityAttribute($entity, $this->primaryKey)) {
            $action = new Insert($this, $entity, $options);
        } else {
            $action = new Update($this, $entity, $options);
        }

        return $this->behaviours->apply($this, 'save', $action);
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

        return $this->behaviours->apply($this, 'delete', $action);
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

    public function getReadConnection()
    {
        return $this->orm->getConnectionLocator()->getRead();
    }

    public function getWriteConnection()
    {
        return $this->orm->getConnectionLocator()->getWrite();
    }

    public function getCasts()
    {
        return $this->casts;
    }
}
