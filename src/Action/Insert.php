<?php
declare(strict_types=1);

namespace Sirius\Orm\Action;

use Sirius\Orm\Entity\StateEnum;

class Insert extends BaseAction
{
    private $entityId;
    private $entityState;

    protected function execute()
    {
        $this->entityId    = $this->entity->getPk();
        $this->entityState = $this->entity->getPersistanceState();
        /**
         * @todo implement INSERT query
         */
        $this->entity->setPk(/*$lastInsertId*/);
        $this->entity->setPersistanceState(StateEnum::SYNCHRONIZED);
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
