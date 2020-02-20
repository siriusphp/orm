<?php
declare(strict_types=1);

namespace Sirius\Orm\Action;

use Sirius\Orm\Entity\StateEnum;
use Sirius\Orm\Helpers\Arr;

class Update extends BaseAction
{
    protected $entityId;
    protected $entityState;

    protected $extraColumns = [];

    /**
     * Add extra columns to be added to the insert.
     * To be used by behaviours relations
     *
     * @param array $columns
     *
     * @return self
     */
    public function addColumns(array $columns)
    {
        foreach ($columns as $name => $value) {
            $this->extraColumns[$name] = $value;
        }

        return $this;
    }

    protected function execute()
    {
        $this->entityId    = $this->entity->getPk();
        $this->entityState = $this->entity->getPersistenceState();

        $connection = $this->mapper->getWriteConnection();

        $columns = $this->mapper->extractFromEntity($this->entity);
        $columns = Arr::only($columns, array_keys($this->entity->getChanges()));
        $columns = array_merge(
            $columns,
            $this->extraColumns,
            $this->mapper->getGuards()
        );
        $columns = Arr::except($columns, $this->mapper->getPrimaryKey());

        if (count($columns) > 0) {
            $updateSql = new \Sirius\Sql\Update($connection);
            $updateSql->table($this->mapper->getTable())
                      ->columns($columns)
                      ->where($this->mapper->getPrimaryKey(), $this->entity->getPk());
            $updateSql->perform();
        }
    }

    public function revert()
    {
        if (! $this->hasRun) {
            return;
        }
        $this->entity->setPersistenceState($this->entityState);
    }

    public function onSuccess()
    {
        foreach ($this->extraColumns as $col => $value) {
            $this->mapper->setEntityAttribute($this->entity, $col, $value);
        }
        $this->entity->setPersistenceState(StateEnum::SYNCHRONIZED);
    }
}
