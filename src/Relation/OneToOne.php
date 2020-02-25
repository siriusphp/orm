<?php

namespace Sirius\Orm\Relation;

use Sirius\Orm\Action\BaseAction;
use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\Entity\StateEnum;

class OneToOne extends OneToMany
{
    public function attachMatchesToEntity(EntityInterface $nativeEntity, array $result)
    {
        // no point in linking entities if the native one is deleted
        if ($nativeEntity->getPersistenceState() == StateEnum::DELETED) {
            return;
        }

        $found = null;
        foreach ($result as $foreignEntity) {
            if ($this->entitiesBelongTogether($nativeEntity, $foreignEntity)) {
                $found = $foreignEntity;
                $this->attachEntities($nativeEntity, $foreignEntity);
                break;
            }
        }

        $this->nativeMapper->setEntityAttribute($nativeEntity, $this->name, $found);
    }

    protected function addActionOnDelete(BaseAction $action)
    {
        // no cascade delete? treat it as a save
        if (! $this->isCascade()) {
            $this->addActionOnSave($action);
        } else {
            $foreignEntity = $this->nativeMapper
                                  ->getEntityAttribute($action->getEntity(), $this->name);

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
        if (! $this->relationWasChanged($action->getEntity())) {
            return;
        }
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
