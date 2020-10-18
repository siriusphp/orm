<?php

namespace Sirius\Orm\Relation;

use Sirius\Orm\Action\BaseAction;
use Sirius\Orm\Contract\EntityInterface;
use Sirius\Orm\Entity\StateEnum;

class OneToOne extends OneToMany
{
    public function attachMatchesToEntity(EntityInterface $nativeEntity, array $result)
    {
        // no point in linking entities if the native one is deleted
        if ($nativeEntity->getState() == StateEnum::DELETED) {
            return;
        }

        $nativeId = $this->getEntityId($this->nativeMapper, $nativeEntity, array_keys($this->keyPairs));

        $found = $result[$nativeId] ?? [];

        $this->nativeEntityHydrator->set($nativeEntity, $this->name, $found[0] ?? null);
    }

    protected function addActionOnDelete(BaseAction $action)
    {
        // no cascade delete? treat it as a save
        if ( ! $this->isCascade()) {
            $this->addActionOnSave($action);
        } else {
            $foreignEntity = $this->nativeEntityHydrator->get($action->getEntity(), $this->name);

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
        if ( ! $this->relationWasChanged($action->getEntity())) {
            return;
        }

        if ( ! $action->includesRelation($this->name)) {
            return;
        }

        $foreignEntity = $this->nativeEntityHydrator->get($action->getEntity(), $this->name);
        if ($foreignEntity) {
            $remainingRelations = $this->getRemainingRelations($action->getOption('relations'));

            $saveAction = $this->foreignMapper->newSaveAction($foreignEntity, ['relations' => $remainingRelations]);
            $saveAction->addColumns($this->getExtraColumnsForAction());
            $action->prepend($saveAction);
            $action->append($this->newSyncAction($action->getEntity(), $foreignEntity, 'save'));
        }
    }
}
