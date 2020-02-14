<?php
declare(strict_types=1);

namespace Sirius\Orm\Relation;

use Sirius\Orm\Action\AttachEntities;
use Sirius\Orm\Action\BaseAction;
use Sirius\Orm\Action\Delete;
use Sirius\Orm\Action\DetachEntities;
use Sirius\Orm\Action\Update;
use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\Entity\LazyValueLoader;
use Sirius\Orm\Entity\Tracker;
use Sirius\Orm\Helpers\Arr;
use Sirius\Orm\LazyLoader;
use Sirius\Orm\Mapper;

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
     * @var array
     */
    protected $keyPairs;

    public function __construct($name, Mapper $nativeMapper, Mapper $foreignMapper, array $options = [])
    {
        $this->name          = $name;
        $this->nativeMapper  = $nativeMapper;
        $this->foreignMapper = $foreignMapper;
        $this->options       = $options;
        $this->applyDefaults();
        $this->keyPairs = $this->computeKeyPairs();
    }

    protected function applyDefaults(): void
    {
        $this->setOptionIfMissing(RelationOption::LOAD_STRATEGY, RelationOption::LOAD_LAZY);
        $this->setOptionIfMissing(RelationOption::CASCADE, false);
    }

    protected function setOptionIfMissing($name, $value)
    {
        if (! isset($this->options[$name])) {
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
     * Checks if a native entity belongs and a foreign entity belong together according to this relation
     * It verifies if the attributes are properly linked
     *
     * @param EntityInterface $nativeEntity
     * @param EntityInterface $foreignEntity
     *
     * @return mixed
     */
    public function entitiesBelongTogether(EntityInterface $nativeEntity, EntityInterface $foreignEntity)
    {
        foreach ($this->keyPairs as $nativeCol => $foreignCol) {
            $nativeKeyValue  = $this->nativeMapper->getEntityAttribute($nativeEntity, $nativeCol);
            $foreignKeyValue = $this->foreignMapper->getEntityAttribute($foreignEntity, $foreignCol);
            // if both native and foreign key values are present (not unlinked entities) they must be the same
            // otherwise we assume that the entities can be linked together
            if ($nativeKeyValue && $foreignKeyValue && $nativeKeyValue != $foreignKeyValue) {
                return false;
            }
        }

        return true;
    }

    public function isEagerLoad()
    {
        return $this->options[RelationOption::LOAD_STRATEGY] == RelationOption::LOAD_EAGER;
    }

    public function isLazyLoad()
    {
        return $this->options[RelationOption::LOAD_STRATEGY] == RelationOption::LOAD_LAZY;
    }

    public function isCascade()
    {
        return $this->options[RelationOption::CASCADE] === true;
    }

    protected function getKeyColumn($name, $column)
    {
        if (is_array($column)) {
            $keyColumn = [];
            foreach ($column as $col) {
                $keyColumn[] = $name . '_' . $col;
            }

            return $keyColumn;
        }

        return $name . '_' . $column;
    }

    public function addActions(BaseAction $action)
    {
        if (! $this->cascadeIsAllowedForAction($action)) {
            return;
        }

        if ($action instanceof Delete) {
            $this->addActionOnDelete($action);
        } elseif ($action instanceof Insert || $action instanceof Update) {
            $this->addActionOnSave($action);
        }
    }

    abstract public function attachMatchesToEntity(EntityInterface $nativeEntity, array $queryResult);

    abstract public function detachEntities(EntityInterface $nativeEntity, EntityInterface $foreignEntity);

    public function attachLazyValueToEntity(EntityInterface $entity, Tracker $tracker)
    {
        $valueLoader = new LazyValueLoader($entity, $tracker, $this);
        $this->nativeMapper->setEntityAttribute($entity, $this->name, $valueLoader);
    }

    public function getQuery(Tracker $tracker)
    {
        $nativeKey = $this->options[RelationOption::NATIVE_KEY];
        $nativePks = $tracker->pluck($nativeKey);

        $query = $this->foreignMapper
            ->newQuery()
            ->where($this->foreignMapper->getPrimaryKey(), $nativePks);

        if ($this->getOption(RelationOption::QUERY_CALLBACK) &&
            is_callable($this->getOption(RelationOption::QUERY_CALLBACK))) {
            $callback = $this->options[RelationOption::QUERY_CALLBACK];
            $query    = $callback($query);
        }

        if ($this->getOption(RelationOption::FOREIGN_GUARDS)) {
            $query->setGuards($this->options[RelationOption::FOREIGN_GUARDS]);
        }

        return $query;
    }

    /**
     * Method used by `entitiesBelongTogether` to check
     * if a foreign entity belongs to the native entity
     * @return array
     */
    protected function computeKeyPairs()
    {
        $pairs      = [];
        $nativeKey  = (array)$this->options[RelationOption::NATIVE_KEY];
        $foreignKey = (array)$this->options[RelationOption::FOREIGN_KEY];
        foreach ($nativeKey as $k => $v) {
            $pairs[$v] = $foreignKey[$k];
        }

        return $pairs;
    }

    /**
     * @param BaseAction $action
     *
     * @return bool|mixed|null
     * @see BaseAction::$options
     */
    protected function cascadeIsAllowedForAction(BaseAction $action)
    {
        $relations = $action->getOption('relations');
        if (is_array($relations) && ! in_array($this->name, $relations)) {
            return false;
        }

        return $relations;
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
        if (! is_array($relations)) {
            return $relations;
        }

        $children = Arr::getChildren(array_combine($relations, $relations), $this->name);

        return array_keys($children);
    }

    protected function getExtraColumnsForAction()
    {
        $cols   = [];
        $guards = $this->getOption(RelationOption::FOREIGN_GUARDS);
        if (is_array($guards)) {
            foreach ($guards as $col => $val) {
                // guards that are strings (eg: 'deleted_at is null') can't be used as extra columns
                if (! is_int($col)) {
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
                $nativeEntity,
                $foreignEntity,
                $this,
                'save'
            );
        }

        return new AttachEntities(
            $nativeEntity,
            $foreignEntity,
            $this,
            'save'
        );
    }
}
