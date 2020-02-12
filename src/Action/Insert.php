<?php
declare(strict_types=1);

namespace Sirius\Orm\Action;

use Sirius\Orm\Entity\StateEnum;
use Sirius\Orm\Helpers\Arr;

class Insert extends BaseAction
{
    private $entityId;
    private $entityState;

    private $extraColumns = [];

    /**
     * Add extra columns to be added to the insert.
     * To be used by behaviours (eg: Timestamps)
     *
     * @param array $columns
     */
    public function addColumns(array $columns)
    {
        $this->extraColumns = array_merge($this->extraColumns, $columns);
    }

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
        $this->lastInsertId = $connection->lastInsertId();
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
        $this->entity->setPk($this->lastInsertId);
        $this->entity->setPersistanceState(StateEnum::SYNCHRONIZED);
    }
}
