<?php
declare(strict_types=1);

namespace Sirius\Orm\Action;

use Sirius\Orm\Entity\StateEnum;

class Delete extends BaseAction
{
    private $entityId;
    private $entityState;

    protected function execute()
    {
        $this->entityId    = $this->entity->getPk();
        $this->entityState = $this->entity->getPersistanceState();

        $delete = new \Sirius\Sql\Delete($this->orm->getConnectionLocator()->getWrite());
        $delete->from($this->mapper->getTable())
               ->where('id', $this->entityId);
        $delete->perform();

        $this->entity->setPk(null);
        $this->entity->setPersistanceState(StateEnum::DELETED);
    }

    public function revert()
    {
        if (! $this->hasRun) {
            return;
        }
        $this->entity->setPK($this->entityId);
        $this->entity->setPersistanceState($this->entityState);
    }
}
