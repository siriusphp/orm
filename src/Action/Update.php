<?php
declare(strict_types=1);

namespace Sirius\Orm\Action;

use Sirius\Orm\Entity\StateEnum;
use Sirius\Orm\Helpers\Arr;

class Update extends Save
{
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
        $conditions = $this->getConditions();

        if (empty($conditions)) {
            return;
        }

        $this->entityState = $this->entity->getState();

        $connection = $this->connection;

        $columns = $this->mapper->extractFromEntity($this->entity);
        $changes = Arr::renameKeys($this->entity->getChanges(), array_flip($this->mapper->getConfig()->getColumnAttributeMap()));
        $columns = Arr::only($columns, array_keys($changes));
        $columns = array_merge(
            $columns,
            $this->extraColumns,
            $this->mapper->getConfig()->getGuards()
        );
        $columns = Arr::except($columns, $this->mapper->getConfig()->getPrimaryKey());

        if (count($columns) > 0) {
            $updateSql = new \Sirius\Sql\Update($connection);
            $updateSql->table($this->mapper->getConfig()->getTable())
                      ->columns($columns)
                      ->whereAll($conditions, false);
            $updateSql->perform();
        }
    }

    public function revert()
    {
        if ( ! $this->hasRun) {
            return;
        }
        $this->entity->setState($this->entityState);
    }

    public function onSuccess()
    {
        foreach ($this->extraColumns as $col => $value) {
            $this->mapper->setEntityAttribute($this->entity, $col, $value);
        }
        $this->entity->setState(StateEnum::SYNCHRONIZED);
    }
}
