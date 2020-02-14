<?php

namespace Sirius\Orm\Relation;

use Sirius\Orm\Action\BaseAction;
use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\Entity\StateEnum;

class ManyToOne extends Relation
{
    protected function applyDefaults(): void
    {
        parent::applyDefaults();

        $foreignKey = $this->foreignMapper->getPrimaryKey();
        if (! isset($this->options[RelationOption::FOREIGN_KEY])) {
            $this->options[RelationOption::FOREIGN_KEY] = $foreignKey;
        }

        if (! isset($this->options[RelationOption::NATIVE_KEY])) {
            $nativeKey                                 = $this->getKeyColumn($this->name, $foreignKey);
            $this->options[RelationOption::NATIVE_KEY] = $nativeKey;
        }
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
        if ($nativeEntity->getPersistanceState() == StateEnum::DELETED) {
            return;
        }

        $nativeKey  = (array)$this->getOption(RelationOption::NATIVE_KEY);
        $foreignKey = (array)$this->getOption(RelationOption::FOREIGN_KEY);

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
        // required for DELETED entities that throw errors if they are changed
        $state = $foreignEntity->getPersistanceState();
        $foreignEntity->setPersistanceState(StateEnum::SYNCHRONIZED);

        $nativeKey  = (array)$this->getOption(RelationOption::NATIVE_KEY);
        $foreignKey = (array)$this->getOption(RelationOption::FOREIGN_KEY);

        foreach ($nativeKey as $k => $col) {
            $this->nativeMapper->setEntityAttribute(
                $nativeEntity,
                $col,
                null
            );
        }

        $this->nativeMapper->setEntityAttribute($nativeEntity, $this->name, null);
        $state = $foreignEntity->getPersistanceState();
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
                $action->append($this->newSyncAction($action->getEntity(), $foreignEntity, 'delete'));
            }
        }
    }

    protected function addActionOnSave(BaseAction $action)
    {
        $foreignEntity = $this->nativeMapper->getEntityAttribute($action->getEntity(), $this->name);
        if ($foreignEntity) {
            $remainingRelations = $this->getRemainingRelations($action->getOption('relations'));
            $saveAction         = $this->foreignMapper
                ->newSaveAction($foreignEntity, ['relations' => $remainingRelations]);
            $saveAction->addColumns($this->getExtraColumnsForAction());
            $action->prepend($saveAction);
            $action->append($this->newSyncAction($action->getEntity(), $foreignEntity, 'save'));
        }
    }
}
