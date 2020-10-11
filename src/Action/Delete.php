<?php
declare(strict_types=1);

namespace Sirius\Orm\Action;

use Sirius\Orm\Connection;
use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\Entity\StateEnum;
use Sirius\Orm\Mapper;

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

    public function onSuccess()
    {
        if ($this->entity->getState() !== StateEnum::DELETED) {
            $this->entityHydrator->setPk($this->entity, null);
            $this->entity->setState(StateEnum::DELETED);
        }
    }
}
