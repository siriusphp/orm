<?php

namespace Sirius\Orm\Relation;

use Sirius\Orm\Action\BaseAction;
use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\Entity\StateEnum;
use Sirius\Orm\Query;

class ManyToOne extends Relation
{
    protected function applyDefaults(): void
    {
        parent::applyDefaults();

        $foreignKey = $this->foreignMapper->getPrimaryKey();
        if (! isset($this->options[RelationConfig::FOREIGN_KEY])) {
            $this->options[RelationConfig::FOREIGN_KEY] = $foreignKey;
        }

        if (! isset($this->options[RelationConfig::NATIVE_KEY])) {
            $nativeKey                                 = $this->getKeyColumn($this->name, $foreignKey);
            $this->options[RelationConfig::NATIVE_KEY] = $nativeKey;
        }
    }

    public function joinSubselect(Query $query, string $reference)
    {
        $tableRef = $this->foreignMapper->getTableAlias(true);
        $subselect = $query->subSelectForJoinWith()
                           ->from($this->foreignMapper->getTable())
                           ->columns($this->foreignMapper->getTable() . '.*')
                           ->as($reference);

        $subselect = $this->applyQueryCallback($subselect);

        $subselect = $this->applyForeignGuards($subselect);

        return $query->join('INNER', $subselect->getStatement(), $this->getJoinOnForSubselect());
    }

    public function attachMatchesToEntity(EntityInterface $nativeEntity, array $result)
    {
        $found = null;
        foreach ($result as $foreignEntity) {
            if ($this->entitiesBelongTogether($nativeEntity, $foreignEntity)) {
                $found = $foreignEntity;
                break;
            }
        }

        $this->attachEntities($nativeEntity, $found);
    }

    /**
     * @param EntityInterface $nativeEntity
     * @param EntityInterface $foreignEntity
     */
    public function attachEntities(EntityInterface $nativeEntity, EntityInterface $foreignEntity = null): void
    {
        // no point in linking entities if the native one is deleted
        if ($nativeEntity->getPersistenceState() == StateEnum::DELETED) {
            return;
        }

        $nativeKey  = (array)$this->getOption(RelationConfig::NATIVE_KEY);
        $foreignKey = (array)$this->getOption(RelationConfig::FOREIGN_KEY);

        foreach ($nativeKey as $k => $col) {
            $this->nativeMapper->setEntityAttribute(
                $nativeEntity,
                $col,
                $foreignEntity ? $this->foreignMapper->getEntityAttribute($foreignEntity, $foreignKey[$k]) : null
            );
        }

        $this->nativeMapper->setEntityAttribute($nativeEntity, $this->name, $foreignEntity);
    }

    public function detachEntities(EntityInterface $nativeEntity, EntityInterface $foreignEntity)
    {
        if ($nativeEntity->getPersistenceState() == StateEnum::DELETED) {
            return;
        }

        // required for DELETED entities that throw errors if they are changed
        $state = $foreignEntity->getPersistenceState();
        $foreignEntity->setPersistenceState(StateEnum::SYNCHRONIZED);

        $nativeKey  = (array)$this->getOption(RelationConfig::NATIVE_KEY);
        $foreignKey = (array)$this->getOption(RelationConfig::FOREIGN_KEY);

        foreach ($nativeKey as $k => $col) {
            $this->nativeMapper->setEntityAttribute(
                $nativeEntity,
                $col,
                null
            );
        }

        $this->nativeMapper->setEntityAttribute($nativeEntity, $this->name, null);
        $state = $foreignEntity->getPersistenceState();
    }

    protected function addActionOnDelete(BaseAction $action)
    {
        // no cascade delete? treat it as a save
        if (! $this->isCascade()) {
            $this->addActionOnSave($action);
        } else {
            $nativeEntity  = $action->getEntity();
            $foreignEntity = $nativeEntity->get($this->name);

            if ($foreignEntity) {
                $remainingRelations = $this->getRemainingRelations($action->getOption('relations'));
                $deleteAction       = $this->foreignMapper
                    ->newDeleteAction($foreignEntity, ['relations' => $remainingRelations]);
                $action->prepend($deleteAction);
                $action->prepend($this->newSyncAction($action->getEntity(), $foreignEntity, 'delete'));
            }
        }
    }

    protected function addActionOnSave(BaseAction $action)
    {
        if (!$this->relationWasChanged($action->getEntity())) {
            return;
        }
        $foreignEntity = $this->nativeMapper->getEntityAttribute($action->getEntity(), $this->name);
        if ($foreignEntity) {
            $remainingRelations = $this->getRemainingRelations($action->getOption('relations'));
            $saveAction         = $this->foreignMapper
                ->newSaveAction($foreignEntity, ['relations' => $remainingRelations]);
            $saveAction->addColumns($this->getExtraColumnsForAction());
            $action->prepend($saveAction);
            $action->prepend($this->newSyncAction($action->getEntity(), $foreignEntity, 'save'));
        }
    }
}
