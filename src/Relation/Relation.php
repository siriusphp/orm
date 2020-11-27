<?php
declare(strict_types=1);

namespace Sirius\Orm\Relation;

use Sirius\Orm\Action\AttachEntities;
use Sirius\Orm\Action\BaseAction;
use Sirius\Orm\Action\Delete;
use Sirius\Orm\Action\DetachEntities;
use Sirius\Orm\Action\Insert;
use Sirius\Orm\Action\Update;
use Sirius\Orm\Contract\EntityInterface;
use Sirius\Orm\Contract\HydratorInterface;
use Sirius\Orm\Entity\Tracker;
use Sirius\Orm\Helpers\Arr;
use Sirius\Orm\Helpers\QueryHelper;
use Sirius\Orm\Mapper;
use Sirius\Orm\Query;

abstract class Relation
{
    /**
     * Name of the relation (used to infer defaults)
     * @var
     */
    protected $name;

    /**
     * @var Mapper
     */
    protected $nativeMapper;

    /**
     * @var Mapper
     */
    protected $foreignMapper;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * Stores the nativeColumn-foreignColumn pairs to be used on queries
     * @var array
     */
    protected $keyPairs;

    /**
     * @var HydratorInterface
     */
    protected $nativeEntityHydrator;

    /**
     * @var HydratorInterface
     */
    protected $foreignEntityHydrator;

    public function __construct($name, Mapper $nativeMapper, Mapper $foreignMapper, array $options = [])
    {
        $this->nativeMapper  = $nativeMapper;
        $this->foreignMapper = $foreignMapper;
        $this->name          = $name;
        $this->options       = $options;
        $this->applyDefaults();
        $this->keyPairs = $this->computeKeyPairs();

        $this->nativeEntityHydrator  = $nativeMapper->getHydrator();
        $this->foreignEntityHydrator = $foreignMapper->getHydrator();
    }

    protected function applyDefaults(): void
    {
        $this->setOptionIfMissing(RelationConfig::LOAD_STRATEGY, RelationConfig::LOAD_LAZY);
    }

    protected function setOptionIfMissing($name, $value)
    {
        if ( ! isset($this->options[$name])) {
            $this->options[$name] = $value;
        }
    }

    public function getOption($name)
    {
        if ($name == 'name') {
            return $this->name;
        }

        return $this->options[$name] ?? null;
    }

    /**
     * @return array
     */
    public function getKeyPairs(): array
    {
        return $this->keyPairs;
    }

    public function isEagerLoad()
    {
        return $this->options[RelationConfig::LOAD_STRATEGY] == RelationConfig::LOAD_EAGER;
    }

    public function isLazyLoad()
    {
        return $this->options[RelationConfig::LOAD_STRATEGY] == RelationConfig::LOAD_LAZY;
    }

    public function isCascade()
    {
        return $this->options[RelationConfig::CASCADE] === true;
    }

    protected function getKeyColumn($name, $column)
    {
        if ( ! is_array($column)) {
            return $name . '_' . $column;
        }

        $keyColumn = [];
        foreach ($column as $col) {
            $keyColumn[] = $name . '_' . $col;
        }

        return $keyColumn;
    }

    public function addActions(BaseAction $action)
    {
        if ($action instanceof Delete) {
            $this->addActionOnDelete($action);
        } elseif ($action instanceof Insert || $action instanceof Update) {
            $this->addActionOnSave($action);
        }
    }

    abstract protected function addActionOnSave(BaseAction $action);

    abstract protected function addActionOnDelete(BaseAction $action);

    abstract public function attachMatchesToEntity(EntityInterface $nativeEntity, array $queryResult);

    abstract public function attachEntities(EntityInterface $nativeEntity, EntityInterface $foreignEntity);

    abstract public function detachEntities(EntityInterface $nativeEntity, EntityInterface $foreignEntity);

    abstract public function joinSubselect(Query $query, string $reference);

    public function attachLazyRelationToEntity(EntityInterface $entity, Tracker $tracker)
    {
        $valueLoader = $tracker->getLazyRelation($this);
        $this->nativeEntityHydrator->setLazy($entity, $this->name, $valueLoader);
    }

    public function getQuery(Tracker $tracker)
    {
        $nativeKey = $this->options[RelationConfig::NATIVE_KEY];
        $nativePks = $tracker->pluck($nativeKey);

        $query = $this->foreignMapper
            ->newQuery()
            ->where($this->foreignMapper->getConfig()->getPrimaryKey(), $nativePks);

        $query = $this->applyQueryCallback($query);

        $query = $this->applyForeignGuards($query);

        return $query;
    }

    public function indexQueryResults(array $entities)
    {
        $result = [];

        foreach ($entities as $entity) {
            $entityId = $this->getEntityId($this->foreignMapper, $entity, array_values($this->keyPairs));
            if ( ! isset($result[$entityId])) {
                $result[$entityId] = [];
            }
            $result[$entityId][] = $entity;
        }

        return $result;
    }

    protected function getEntityId(Mapper $mapper, EntityInterface $entity, array $keyColumns)
    {
        $entityKeys = [];
        foreach ($keyColumns as $col) {
            $entityKeys[] = $mapper->getHydrator()->get($entity, $col);
        }

        return implode('-', $entityKeys);
    }

    /**
     * Method used by `entitiesBelongTogether` to check
     * if a foreign entity belongs to the native entity
     * @return array
     */
    protected function computeKeyPairs()
    {
        $pairs      = [];
        $nativeKey  = (array)$this->options[RelationConfig::NATIVE_KEY];
        $foreignKey = (array)$this->options[RelationConfig::FOREIGN_KEY];
        foreach ($nativeKey as $k => $v) {
            $pairs[$v] = $foreignKey[$k];
        }

        return $pairs;
    }

    /**
     * Computes the $withRelations value to be passed on to the next related entities
     * If an entity receives on delete/save $withRelations = ['category', 'category.images']
     * the related 'category' is saved with $withRelations = ['images']
     *
     * @param $relations
     *
     * @return array
     */
    protected function getRemainingRelations($relations)
    {
        if ( ! is_array($relations)) {
            return $relations;
        }

        $arr = array_combine($relations, $relations);
        if (is_array($arr)) {
            $children = Arr::getChildren($arr, $this->name);

            return array_keys($children);
        }

        return [];
    }

    protected function getExtraColumnsForAction()
    {
        $cols   = [];
        $guards = $this->getOption(RelationConfig::FOREIGN_GUARDS);
        if (is_array($guards)) {
            foreach ($guards as $col => $val) {
                // guards that are strings (eg: 'deleted_at is null') can't be used as extra columns
                if ( ! is_int($col)) {
                    $cols[$col] = $val;
                }
            }
        }

        return $cols;
    }

    protected function newSyncAction(EntityInterface $nativeEntity, EntityInterface $foreignEntity, string $actionType)
    {
        if ($actionType == 'delete') {
            return new DetachEntities(
                $this->nativeMapper,
                $nativeEntity,
                $this->foreignMapper,
                $foreignEntity,
                $this,
                'save'
            );
        }

        return new AttachEntities(
            $this->nativeMapper,
            $nativeEntity,
            $this->foreignMapper,
            $foreignEntity,
            $this,
            'save'
        );
    }

    protected function relationWasChanged(EntityInterface $entity)
    {
        $changes = $entity->getChanges();

        return isset($changes[$this->name]) && $changes[$this->name];
    }

    protected function applyQueryCallback(Query $query)
    {
        $queryCallback = $this->getOption(RelationConfig::QUERY_CALLBACK);
        if ($queryCallback && is_callable($queryCallback)) {
            $query = $queryCallback($query);
        }

        return $query;
    }

    protected function applyForeignGuards(Query $query)
    {
        $guards = $this->getOption(RelationConfig::FOREIGN_GUARDS);
        if ($guards) {
            $query->setGuards($guards);
        }

        return $query;
    }

    protected function getJoinOnForSubselect()
    {
        return QueryHelper::joinCondition(
            $this->nativeMapper->getConfig()->getTableAlias(true),
            $this->getOption(RelationConfig::NATIVE_KEY),
            $this->name,
            $this->getOption(RelationConfig::FOREIGN_KEY)
        );
    }

    /**
     * @return Mapper
     */
    public function getNativeMapper(): Mapper
    {
        return $this->nativeMapper;
    }

    /**
     * @return Mapper
     */
    public function getForeignMapper(): Mapper
    {
        return $this->foreignMapper;
    }
}
