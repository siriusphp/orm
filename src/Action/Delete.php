<?php
declare(strict_types=1);

namespace Sirius\Orm\Action;

use Sirius\Orm\Entity\StateEnum;

class Delete extends BaseAction
{
    protected function execute()
    {
        $entityId = $this->entity->getPk();
        if (!$entityId) {
            return;
        }

        $delete = new \Sirius\Sql\Delete($this->mapper->getWriteConnection());
        $delete->from($this->mapper->getTable())
               ->where('id', $entityId);
        $delete->perform();
    }

    public function revert()
    {
        return; // no change to the entity has actually been performed
    }

    public function onSuccess()
    {
        if ($this->entity->getPersistenceState() !== StateEnum::DELETED) {
            $this->entity->setPk(null);
            $this->entity->setPersistenceState(StateEnum::DELETED);
        }
    }
}
