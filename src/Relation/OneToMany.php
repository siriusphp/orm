<?php

namespace Sirius\Orm\Relation;

use Sirius\Orm\Action\BaseAction;
use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\Entity\StateEnum;
use Sirius\Orm\Entity\Tracker;
use Sirius\Orm\Helpers\Inflector;
use Sirius\Orm\Query;

class OneToMany extends Relation
{
    use HasAggregates;

    protected function applyDefaults(): void
    {
        $nativeKey = $this->nativeMapper->getPrimaryKey();
        if (! isset($this->options[RelationConfig::NATIVE_KEY])) {
            $this->options[RelationConfig::NATIVE_KEY] = $nativeKey;
        }

        if (! isset($this->options[RelationConfig::FOREIGN_KEY])) {
            $prefix                                     = Inflector::singularize($this->nativeMapper->getTable());
            $this->options[RelationConfig::FOREIGN_KEY] = $this->getKeyColumn($prefix, $nativeKey);
        }

        parent::applyDefaults();
    }

    public function getQuery(Tracker $tracker)
    {
        $nativeKey = $this->options[RelationConfig::NATIVE_KEY];
        $nativePks = $tracker->pluck($nativeKey);

        $query = $this->foreignMapper
            ->newQuery()
            ->where($this->options[RelationConfig::FOREIGN_KEY], $nativePks);

        $query = $this->applyQueryCallback($query);

        $query = $this->applyForeignGuards($query);

        return $query;
    }

    public function joinSubselect(Query $query, string $reference)
    {
        $subselect = $query->subSelectForJoinWith()
                           ->columns($this->foreignMapper->getTable() . '.*')
                           ->from($this->foreignMapper->getTable())
                           ->as($reference);

        $subselect = $this->applyQueryCallback($subselect);

        $subselect = $this->applyForeignGuards($subselect);

        return $query->join('INNER', $subselect->getStatement(), $this->getJoinOnForSubselect());
    }

    public function attachMatchesToEntity(EntityInterface $nativeEntity, array $result)
    {
        // no point in linking entities if the native one is deleted
        if ($nativeEntity->getPersistenceState() == StateEnum::DELETED) {
            return;
        }

        $nativeId = $this->getEntityId($this->nativeMapper, $nativeEntity, array_keys($this->keyPairs));

        $found = $result[$nativeId] ?? [];

        $this->nativeMapper->setEntityAttribute($nativeEntity, $this->name, new Collection($found));
    }

    public function attachEntities(EntityInterface $nativeEntity, EntityInterface $foreignEntity)
    {
        foreach ($this->keyPairs as $nativeCol => $foreignCol) {
            $nativeKeyValue  = $this->nativeMapper->getEntityAttribute($nativeEntity, $nativeCol);
            $this->foreignMapper->setEntityAttribute($foreignEntity, $foreignCol, $nativeKeyValue);
        }
    }

    public function detachEntities(EntityInterface $nativeEntity, EntityInterface $foreignEntity)
    {
        $state = $foreignEntity->getPersistenceState();
        $foreignEntity->setPersistenceState(StateEnum::SYNCHRONIZED);
        foreach ($this->keyPairs as $nativeCol => $foreignCol) {
            $this->foreignMapper->setEntityAttribute($foreignEntity, $foreignCol, null);
        }
        $this->foreignMapper->setEntityAttribute($foreignEntity, $this->name, null);
        $foreignEntity->setPersistenceState($state);
    }

    protected function addActionOnDelete(BaseAction $action)
    {
        $nativeEntity       = $action->getEntity();
        $remainingRelations = $this->getRemainingRelations($action->getOption('relations'));

        // no cascade delete? treat as save so we can process the changes
        if (! $this->isCascade()) {
            $this->addActionOnSave($action);
        } else {
            // retrieve them again from the DB since the related collection might not have everything
            // for example due to a relation query callback
            $foreignEntities = $this->getQuery(new Tracker([$nativeEntity->getArrayCopy()]))
                                    ->get();

            foreach ($foreignEntities as $foreignEntity) {
                $deleteAction = $this->foreignMapper
                    ->newDeleteAction($foreignEntity, ['relations' => $remainingRelations]);
                $action->append($this->newSyncAction($nativeEntity, $foreignEntity, 'delete'));
                $action->append($deleteAction);
            }
        }
    }

    protected function addActionOnSave(BaseAction $action)
    {
        if (!$this->relationWasChanged($action->getEntity())) {
            return;
        }

        $nativeEntity       = $action->getEntity();
        $remainingRelations = $this->getRemainingRelations($action->getOption('relations'));

        /** @var Collection $foreignEntities */
        $foreignEntities = $this->nativeMapper->getEntityAttribute($nativeEntity, $this->name);
        $changes         = $foreignEntities->getChanges();

        // save the entities still in the collection
        foreach ($foreignEntities as $foreignEntity) {
            if (! empty($foreignEntity->getChanges())) {
                $saveAction = $this->foreignMapper
                    ->newSaveAction($foreignEntity, ['relations' => $remainingRelations]);
                $saveAction->addColumns($this->getExtraColumnsForAction());
                $action->append($this->newSyncAction($nativeEntity, $foreignEntity, 'save'));
                $action->append($saveAction);
            }
        }

        // save entities that were removed but NOT deleted
        foreach ($changes['removed'] as $foreignEntity) {
            $saveAction = $this->foreignMapper
                ->newSaveAction($foreignEntity, ['relations' => $remainingRelations]);
            $saveAction->addColumns($this->getExtraColumnsForAction());
            $action->append($this->newSyncAction($nativeEntity, $foreignEntity, 'delete'));
            $action->append($saveAction);
        }
    }
}
