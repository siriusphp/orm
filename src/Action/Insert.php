<?php
declare(strict_types=1);

namespace Sirius\Orm\Action;

use Sirius\Orm\Helpers\Arr;

class Insert extends Update
{
    private $entityId;
    private $entityState;

    private $extraColumns = [];

    protected function execute()
    {
        $this->entityId    = $this->entity->getPk();
        $this->entityState = $this->entity->getPersistanceState();

        $connection = $this->mapper->getWriteConnection();

        $columns = array_merge(
            Arr::only($this->entity->getArrayCopy(), $this->mapper->getColumns()),
            $this->extraColumns,
            $this->mapper->getGuards()
        );
        $columns = Arr::except($columns, $this->mapper->getPrimaryKey());

        $insertSql = new \Sirius\Sql\Insert($connection);
        $insertSql->into($this->mapper->getTable())
                  ->columns($columns);
        $insertSql->perform();
        $this->entity->setPk($connection->lastInsertId());
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
