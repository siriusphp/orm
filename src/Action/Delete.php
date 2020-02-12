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

        $delete = new \Sirius\Sql\Delete($this->mapper->getWriteConnection());
        $delete->from($this->mapper->getTable())
               ->where('id', $this->entityId);
        $delete->perform();
    }

    public function revert()
    {
        if (! $this->hasRun) {
            return;
        }
        $this->entity->setPK($this->entityId);
        $this->entity->setPersistanceState($this->entityState);
    }

    public function onSuccess()
    {
        if ($this->entity->getPersistanceState() !== StateEnum::DELETED) {
            $this->entity->setPk(null);
            $this->entity->setPersistanceState(StateEnum::DELETED);
        }
    }
}
