<?php

namespace Sirius\Orm\Relation;

use Sirius\Orm\Action\BaseAction;

class OneToOne extends ManyToOne
{
    protected function attachToDelete(BaseAction $action)
    {
        parent::attachToDelete($action);
    }

    protected function attachToSave(BaseAction $action)
    {
        parent::attachToSave($action);
    }
}
