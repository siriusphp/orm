<?php

namespace Sirius\Orm\Relation;

use Sirius\Orm\Action\BaseAction;
use Sirius\Orm\Contract\EntityInterface;
use Sirius\Orm\Entity\StateEnum;
use Sirius\Orm\Entity\Tracker;

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
        $relations          = $action->getOption('relations');

        // no cascade delete? treat it as a save
        if ( ! $this->isCascade()) {
            $this->addActionOnSave($action);
        } elseif ($relations === true || in_array($this->name, (array)$relations)) {
            $nativeEntity       = $action->getEntity();
            $remainingRelations = $this->getRemainingRelations($relations);

            // retrieve them again from the DB since the related collection might not have everything
            // for example due to a relation query callback
            $foreignEntity = $this->getQuery(new Tracker([$nativeEntity->toArray()]))
                                    ->first();

            if ($foreignEntity) {
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
