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
        $delete->from($this->mapper->getConfig()->getTable());
        $delete->whereAll($conditions, false);

        $delete->perform();
    }

    /**
     * Unsets the entity's PK and sets its state to `deleted`
     * @return mixed|void
     */
    public function onSuccess()
    {
        $this->entityHydrator->setPk($this->entity, null);
        if ($this->entity->getState() !== StateEnum::DELETED) {
            $this->entity->setState(StateEnum::DELETED);
        }
    }
}
