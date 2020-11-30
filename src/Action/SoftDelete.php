<?php
declare(strict_types=1);

namespace Sirius\Orm\Action;

use Sirius\Orm\Entity\StateEnum;

class SoftDelete extends Delete
{
    /**
     * @var int
     */
    protected $now;

    protected function execute()
    {
        $entityId = $this->entityHydrator->getPk($this->entity);
        if (! $entityId) {
            return;
        }

        $this->now = date('Y-m-d H:i:s', time());

        $update = new \Sirius\Sql\Update($this->mapper->getWriteConnection());
        $update->table($this->mapper->getConfig()->getTable())
               ->columns([
                   $this->getOption('deleted_at_column') => $this->now
               ])
               ->where('id', $entityId);
        $update->perform();
    }

    public function onSuccess()
    {
        $this->entityHydrator->set($this->entity, $this->getOption('deleted_at_column'), $this->now);
        if ($this->entity->getState() !== StateEnum::DELETED) {
            $this->entity->setState(StateEnum::DELETED);
        }
    }
}
