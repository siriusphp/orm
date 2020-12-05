<?php

namespace Sirius\Orm\Relation;

use Sirius\Orm\Action\BaseAction;
use Sirius\Orm\Action\DeletePivotRows;
use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Contract\EntityInterface;
use Sirius\Orm\Contract\Relation\ToManyInterface;
use Sirius\Orm\Entity\Tracker;
use Sirius\Orm\Helpers\Inflector;
use Sirius\Orm\Helpers\QueryHelper;
use Sirius\Orm\Query;

class ManyToMany extends Relation implements ToManyInterface
{
    use HasAggregates;

    protected function applyDefaults(): void
    {
        parent::applyDefaults();

        $foreignKey = $this->foreignMapper->getConfig()->getPrimaryKey();
        if (! isset($this->options[RelationConfig::FOREIGN_KEY])) {
            $this->options[RelationConfig::FOREIGN_KEY] = $foreignKey;
        }

        $nativeKey = $this->foreignMapper->getConfig()->getPrimaryKey();
        if (! isset($this->options[RelationConfig::NATIVE_KEY])) {
            $this->options[RelationConfig::NATIVE_KEY] = $nativeKey;
        }

        if (! isset($this->options[RelationConfig::PIVOT_TABLE])) {
            $tables = [$this->foreignMapper->getConfig()->getTable(), $this->nativeMapper->getConfig()->getTable()];
            sort($tables);
            $this->options[RelationConfig::PIVOT_TABLE] = implode('_', $tables);
        }

        if (! isset($this->options[RelationConfig::PIVOT_NATIVE_COLUMN])) {
            $prefix = Inflector::singularize($this->nativeMapper->getConfig()->getTableAlias(true));

            $this->options[RelationConfig::PIVOT_NATIVE_COLUMN] = $this->getKeyColumn($prefix, $nativeKey);
        }

        if (! isset($this->options[RelationConfig::PIVOT_FOREIGN_COLUMN])) {
            $prefix = Inflector::singularize($this->foreignMapper->getConfig()->getTableAlias(true));

            $this->options[RelationConfig::PIVOT_FOREIGN_COLUMN] = $this->getKeyColumn($prefix, $foreignKey);
        }
    }

    public function getQuery(Tracker $tracker)
    {
        $nativeKey = $this->options[RelationConfig::NATIVE_KEY];
        $nativePks = $tracker->pluck($nativeKey, $this->nativeEntityHydrator);

        if (empty($nativePks)) {
            return null;
        }

        $query = $this->foreignMapper->newQuery();

        $query = $this->joinWithPivotTable($query)
                      ->where($this->options[RelationConfig::PIVOT_NATIVE_COLUMN], $nativePks);

        $query = $this->applyQueryCallback($query);

        $query = $this->applyForeignGuards($query);

        $query = $this->applyPivotGuards($query);

        $query = $this->addPivotColumns($query);

        return $query;
    }

    protected function joinWithPivotTable($query)
    {
        $pivotTable      = $this->getOption(RelationConfig::PIVOT_TABLE);
        $pivotTableAlias = $this->getOption(RelationConfig::PIVOT_TABLE_ALIAS);
        $pivotReference  = QueryHelper::reference($pivotTable, $pivotTableAlias);
        $pivotName       = $pivotTableAlias ?? $pivotTable;

        $pivotColumns     = $this->options[RelationConfig::PIVOT_FOREIGN_COLUMN];
        $foreignTableName = $this->foreignMapper->getConfig()->getTableAlias(true);
        $foreignKeys      = $this->options[RelationConfig::FOREIGN_KEY];

        $joinCondition = QueryHelper::joinCondition($foreignTableName, $foreignKeys, $pivotName, $pivotColumns);

        return $query->join('INNER', $pivotReference, $joinCondition);
    }

    private function addPivotColumns($query)
    {
        $pivotColumns = $this->getOption(RelationConfig::PIVOT_COLUMNS);

        $pivotTable      = $this->getOption(RelationConfig::PIVOT_TABLE);
        $pivotTableAlias = $this->getOption(RelationConfig::PIVOT_TABLE_ALIAS);
        $pivotName       = $pivotTableAlias ?? $pivotTable;

        if (! empty($pivotColumns)) {
            foreach ($pivotColumns as $col => $alias) {
                $query->columns("{$pivotName}.{$col} AS {$alias}");
            }
        }

        foreach ((array)$this->options[RelationConfig::PIVOT_NATIVE_COLUMN] as $col) {
            $query->columns("{$pivotName}.{$col}");
        }

        return $query;
    }

    public function joinSubselect(Query $query, string $reference)
    {
        $subselect = $this->foreignMapper->newQuery();
        $subselect = $query->subSelectForJoinWith($this->foreignMapper)
                           ->as($reference);
        #$subselect->resetGuards();
        #$subselect->setGuards($this->foreignMapper->getConfig()->getGuards());

        $subselect = $this->joinWithPivotTable($subselect);

        $subselect = $this->addPivotColumns($subselect);

        $subselect = $this->applyQueryCallback($subselect);

        $subselect = $this->applyForeignGuards($subselect);

        return $query->join('INNER', $subselect->getStatement(), $this->getJoinOnForSubselect());
    }

    protected function getJoinOnForSubselect()
    {
        return QueryHelper::joinCondition(
            $this->nativeMapper->getConfig()->getTableAlias(true),
            $this->getOption(RelationConfig::NATIVE_KEY),
            $this->name,
            $this->getOption(RelationConfig::PIVOT_NATIVE_COLUMN)
        );
    }

    protected function computeKeyPairs()
    {
        $pairs      = [];
        $nativeKey  = (array)$this->options[RelationConfig::NATIVE_KEY];
        $foreignKey = (array)$this->options[RelationConfig::PIVOT_NATIVE_COLUMN];
        foreach ($nativeKey as $k => $v) {
            $pairs[$v] = $foreignKey[$k];
        }

        return $pairs;
    }

    public function attachMatchesToEntity(EntityInterface $nativeEntity, array $result)
    {
        $nativeId = $this->getEntityId($this->nativeMapper, $nativeEntity, array_keys($this->keyPairs));

        $found = $result[$nativeId] ?? [];

        $collection = $this->foreignMapper->newCollection($found);
        $this->nativeEntityHydrator->set($nativeEntity, $this->name, $collection);
    }

    public function attachEntities(EntityInterface $nativeEntity, EntityInterface $foreignEntity)
    {
        foreach ($this->keyPairs as $nativeCol => $foreignCol) {
            $nativeKeyValue = $this->nativeEntityHydrator->get($nativeEntity, $nativeCol);
            $this->foreignEntityHydrator->set($foreignEntity, $foreignCol, $nativeKeyValue);
        }
    }

    public function detachEntities(EntityInterface $nativeEntity, EntityInterface $foreignEntity)
    {
        foreach ($this->keyPairs as $nativeCol => $foreignCol) {
            $this->foreignEntityHydrator->set($foreignEntity, $foreignCol, null);
        }
    }

    protected function addActionOnDelete(BaseAction $action)
    {
        $nativeEntity = $action->getEntity();

        // retrieve them again from the DB since the related collection might not have everything
        // for example due to a relation query callback
        $foreignEntities = $this->getQuery(new Tracker([$nativeEntity->toArray()]))
                                ->get();

        foreach ($foreignEntities as $entity) {
            $deletePivotAction = new DeletePivotRows($this, $nativeEntity, $entity);
            $action->append($deletePivotAction);
        }
    }

    protected function addActionOnSave(BaseAction $action)
    {
        if (! $this->relationWasChanged($action->getEntity())) {
            return;
        }

        if (! $action->includesRelation($this->name)) {
            return;
        }

        $remainingRelations = $this->getRemainingRelations($action->getOption('relations'));

        /** @var Collection $foreignEntities */
        $foreignEntities = $this->nativeEntityHydrator->get($action->getEntity(), $this->name);
        if (! $foreignEntities || ! $foreignEntities instanceof Collection || $foreignEntities->isEmpty()) {
            return;
        }

        $changes = $foreignEntities->getChanges();

        // save the entities still in the collection
        foreach ($foreignEntities as $foreignEntity) {
            if (! empty($foreignEntity->getChanges())) {
                $saveAction = $this->foreignMapper
                    ->newSaveAction($foreignEntity, [
                        'relations' => $remainingRelations
                    ]);
                $saveAction->addColumns($this->getExtraColumnsForAction());
                $action->prepend($saveAction);
                $action->append($this->newSyncAction(
                    $action->getEntity(),
                    $foreignEntity,
                    'save'
                ));
            }
        }

        // save entities that were removed but NOT deleted
        foreach ($changes['removed'] as $foreignEntity) {
            $deletePivotAction = new DeletePivotRows($this, $action->getEntity(), $foreignEntity);
            $action->append($deletePivotAction);
        }
    }

    private function applyPivotGuards(Query $query)
    {
        $guards = $this->getOption(RelationConfig::PIVOT_GUARDS);
        if ($guards) {
            $query->setGuards($guards);
        }

        return $query;
    }
}
