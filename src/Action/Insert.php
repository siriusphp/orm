<?php
declare(strict_types=1);

namespace Sirius\Orm\Action;

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
        $this->entityHydrator->setPk($this->entity, $connection->lastInsertId());
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
