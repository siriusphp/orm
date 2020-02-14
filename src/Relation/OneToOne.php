<?php

namespace Sirius\Orm\Relation;

use Sirius\Orm\Action\BaseAction;
use Sirius\Orm\Entity\EntityInterface;

class OneToOne extends ManyToOne
{
    public function detachEntities(EntityInterface $nativeEntity, EntityInterface $foreignEntity)
    {
        // TODO: Implement detachEntities() method.
    }

    protected function addActionOnDelete(BaseAction $action)
    {
        parent::addActionOnDelete($action);
    }

    protected function addActionOnSave(BaseAction $action)
    {
        parent::addActionOnSave($action);
    }
}
