<?php
declare(strict_types=1);

namespace Sirius\Orm\Action;

use Sirius\Orm\Entity\StateEnum;

class Delete extends BaseAction
{
    protected function execute()
    {
        $conditions = $this->getConditions();

        if (empty($conditions)) {
            return;
        }

        $delete = new \Sirius\Sql\Delete($this->mapper->getWriteConnection());
        $delete->from($this->mapper->getTable());
        $delete->whereAll($conditions, false);

        $delete->perform();
    }

    public function onSuccess()
    {
        if ($this->entity->getPersistenceState() !== StateEnum::DELETED) {
            $this->entity->setPk(null);
            $this->entity->setPersistenceState(StateEnum::DELETED);
        }
    }
}
