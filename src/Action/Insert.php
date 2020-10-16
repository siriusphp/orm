<?php
declare(strict_types=1);

namespace Sirius\Orm\Action;

use Sirius\Orm\Entity\StateEnum;
use Sirius\Orm\Helpers\Arr;

class Insert extends Update
{
    protected $entityId;
    protected $entityState;

    protected $extraColumns = [];

    protected function execute()
    {
        $this->entityId    = $this->entityHydrator->getPk($this->entity);
        $this->entityState = $this->entity->getState();

        $connection = $this->mapper->getWriteConnection();

        $columns = array_merge(
            $this->entityHydrator->extract($this->entity),
            $this->extraColumns,
            $this->mapper->getConfig()->getGuards()
        );
        $columns = Arr::except($columns, $this->mapper->getConfig()->getPrimaryKey());

        $insertSql = new \Sirius\Sql\Insert($connection);
        $insertSql->into($this->mapper->getConfig()->getTable())
                  ->columns($columns);
        $insertSql->perform();

        /**
         * We need to set the ID of the entity here because
         * other actions in the stack might need it
         * For example, on one-to-many relations when persisting the "parent",
         * the actions that persist the "children" have to know about the parent's ID
         */
        $this->entityHydrator->setPk($this->entity, $connection->lastInsertId());
        $this->entity->setState(StateEnum::SYNCHRONIZED);
    }

    public function revert()
    {
        if ( ! $this->hasRun) {
            return;
        }
        $this->entityHydrator->setPk($this->entity, $this->entityId);
        $this->entity->setState($this->entityState);
    }
}
