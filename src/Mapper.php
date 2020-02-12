<?php
declare(strict_types=1);

namespace Sirius\Orm;

use InvalidArgumentException;
use Sirius\Orm\Action\Delete;
use Sirius\Orm\Action\Update;
use Sirius\Orm\Behaviours\BehaviourInterface;
use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Collection\PaginatedCollection;
use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\Entity\GenericEntity;
use Sirius\Orm\Entity\GenericEntityFactory;
use Sirius\Orm\Entity\StateEnum;
use Sirius\Orm\Entity\Tracker;
use Sirius\Orm\Helpers\Arr;
use Sirius\Orm\Helpers\Str;
use Sirius\Orm\Relation\Relation;
use Sirius\Orm\Relation\RelationOption;

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
     * @var FactoryInterface
     */
    protected $entityFactory;

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

    protected $scopes = [];

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
        $mapper                          = new static($orm, $mapperConfig->entityFactory);
        $mapper->table                   = $mapperConfig->table;
        $mapper->tableAlias              = $mapperConfig->tableAlias;
        $mapper->primaryKey              = $mapperConfig->primaryKey;
        $mapper->columns                 = $mapperConfig->columns;
        $mapper->entityDefaultAttributes = $mapperConfig->entityDefaultAttributes;
        $mapper->columnAttributeMap      = $mapperConfig->columnAttributeMap;
        $mapper->scopes                  = $mapperConfig->scopes;

        if ($mapperConfig->entityClass) {
            $mapper->entityClass = $mapperConfig->entityClass;
        }

        if ($mapperConfig->entityFactory && $mapperConfig instanceof FactoryInterface) {
            $mapper->entityFactory = $mapperConfig->entityFactory;
        }

        $mapper->relations = $mapperConfig->relations;
        if ($mapperConfig->behaviours && ! empty($mapperConfig->behaviours)) {
            $mapper->use(...$mapperConfig->behaviours);
        }

        return $mapper;
    }

    public function __construct(Orm $orm, QueryBuilder $queryBuilder = null, FactoryInterface $entityFactory = null)
    {
        $this->orm = $orm;
        if (! $entityFactory) {
            $entityFactory = new GenericEntityFactory($orm, $this);
        }
        if (! $queryBuilder) {
            $this->queryBuilder = new QueryBuilder($orm, $this);
        }
        $this->entityFactory = $entityFactory;
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
     * Add behaviours to the
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
    public function getTableAlias()
    {
        return $this->tableAlias;
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
     * @param $data
     *
     * @return EntityInterface
     */
    public function newEntity(array $data): EntityInterface
    {
        $entity = $this->entityFactory->newInstance(array_merge($this->getEntityDefaults(), $data));

        return $this->applyBehaviours(__FUNCTION__, $entity);
    }

    public function newEntityFromRow(array $data = null, array $load = [], Tracker $tracker = null)
    {
        if ($data == null) {
            return null;
        }

        if (! $tracker) {
            $tracker = new Tracker($this, [$data]);
        }

        $entity = $this->newEntity($data);
        $this->injectRelations($entity, $tracker, $load);
        $entity->setPersistanceState(StateEnum::SYNCHRONIZED);

        return $entity;
    }

    public function newCollectionFromRows(array $rows, array $load = []): Collection
    {
        $entities = [];
        $tracker  = new Tracker($this, $rows);
        foreach ($rows as $row) {
            if ($row === null) {
                continue;
            }
            $entity     = $this->newEntityFromRow($row, $load, $tracker);
            $entities[] = $entity;
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

        return new PaginatedCollection($entities, $totalCount, $perPage, $currentPage);
    }

    protected function injectRelations(EntityInterface $entity, Tracker $tracker, array $eagerLoad = [])
    {
        foreach (array_keys($this->relations) as $name) {
            $relation      = $this->getRelation($name);
            $queryCallback = $eagerLoad[$name] ?? null;
            $nextLoad      = Arr::getChildren($eagerLoad, $name);

            $tracker->setRelation($name, $relation, $queryCallback);

            if (array_key_exists($name, $eagerLoad)) {
                $relation->attachesMatchesToEntity($entity, $tracker->getRelationResults($name));
            } else {
                $relation->attachLazyValueToEntity($entity, $tracker);
            }
        }
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

    public function hasRelation($name)
    {
        return isset($this->relations[$name]);
    }

    public function getRelation($name): Relation
    {
        if (! $this->hasRelation($name)) {
            throw new \InvalidArgumentException("Relation named {$name} is not registered for this mapper");
        }

        if (is_array($this->relations[$name])) {
            $this->relations[$name] = $this->createRelation($name, $this->relations[$name]);
        }
        $relation = $this->relations[$name];
        if (! $relation instanceof Relation) {
            throw new \InvalidArgumentException("Relation named {$name} is not a proper Relation instance");
        }

        return $relation;
    }

    protected function createRelation($name, $options)
    {
        $nativeMapper  = $this;
        $foreignMapper = $options[RelationOption::FOREIGN_MAPPER];
        if (! $foreignMapper instanceof Mapper) {
            $foreignMapper = $this->orm->get($foreignMapper);
        }
        $type          = $options[RelationOption::TYPE];
        $relationClass = __NAMESPACE__ . '\\Relation\\' . Str::className($type);

        if (! class_exists($relationClass)) {
            throw new InvalidArgumentException("{$relationClass} does not exist");
        }

        return new $relationClass($name, $nativeMapper, $foreignMapper, $options);
    }

    public function newQuery(): Query
    {
        $query = $this->queryBuilder->newQuery();

        return $this->applyBehaviours('query', $query);
    }

    /**
     * @param $pk
     *
     * @return EntityInterface|null
     */
    public function find($pk)
    {
        return $this->newQuery()
                    ->where($this->getPrimaryKey(), $pk)
                    ->first();
    }

    /**
     * @param EntityInterface $entity
     *
     * @return
     * @throws \Exception
     */
    public function save(EntityInterface $entity)
    {
        $this->assertCanPersistEntity($entity);

        if (! $entity->pk()) {
            $action = new Insert($this->orm, $this, $entity);
        } else {
            $action = new Update($this->orm, $this, $entity);
        }

        $action = $this->applyBehaviours(__FUNCTION__, $action);

        return $action->run();
    }

    public function delete(EntityInterface $entity)
    {
        $this->assertCanPersistEntity($entity);

        $action = new Delete($this->orm, $this, $entity);
        $action = $this->applyBehaviours(__FUNCTION__, $action);

        return $action->run();
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
}
