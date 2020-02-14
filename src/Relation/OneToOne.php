<?php

namespace Sirius\Orm\Relation;

use Sirius\Orm\Action\BaseAction;

class OneToOne extends ManyToOne
{
    protected function attachToDelete(BaseAction $action)
    {
        // no cascade delete? treat it as a save
        if ( ! $this->isCascade()) {
            $this->attachToSave($action);
        } else {
            $nativeEntity  = $action->getEntity();
            $foreignEntity = $nativeEntity->get($this->name);

            if ($foreignEntity) {
                $remainingRelations = $this->getRemainingRelations($action->getOption('relations'));
                $deleteAction       = $this->foreignMapper
                    ->newDeleteAction($foreignEntity, ['relations' => $remainingRelations]);
                $action->append($deleteAction);
            }
        }
    }

    protected function attachToSave(BaseAction $action)
    {
        $foreignEntity = $this->nativeMapper->getEntityAttribute($action->getEntity(), $this->name);
        if ($foreignEntity) {
            $remainingRelations = $this->getRemainingRelations($action->getOption('relations'));
            $saveAction         = $this->foreignMapper
                ->newSaveAction($foreignEntity, ['relations' => $remainingRelations]);
            $saveAction->addColumns($this->getExtraColumnsForAction());
            $action->append($saveAction);
        }
    }
}
