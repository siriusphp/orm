<?php
declare(strict_types=1);

namespace Sirius\Orm\Action;

use Sirius\Orm\Entity\StateEnum;

class Update extends BaseAction
{
    private $entityState;

    protected function execute()
    {
        $this->entityState = $this->entity->getPersistanceState();
        /**
         * @todo implement UPDATE query
         */
        $this->entity->setPersistanceState(StateEnum::SYNCHRONIZED);
    }

    public function revert()
    {
        if (! $this->hasRun) {
            return;
        }
        $this->entity->setPersistanceState($this->entityState);
    }
}
