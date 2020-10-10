<?php
declare(strict_types=1);

namespace Sirius\Orm\Action;

use Sirius\Orm\Connection;
use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\Entity\StateEnum;
use Sirius\Orm\Mapper;

class Delete extends BaseAction
{
    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(Connection $connection, Mapper $mapper, EntityInterface $entity, array $options = [])
    {
        parent::__construct($mapper, $entity, $options);
        $this->connection = $connection;
    }

    protected function execute()
    {
        $conditions = $this->getConditions();

        if (empty($conditions)) {
            return;
        }

        $delete = new \Sirius\Sql\Delete($this->connection);
        $delete->from($this->mapper->getConfig()->getTable());
        $delete->whereAll($conditions, false);

        $delete->perform();
    }

    public function onSuccess()
    {
        if ($this->entity->getState() !== StateEnum::DELETED) {
            $this->getEntityHydrator()->setPk($this->entity, null);
            $this->entity->setState(StateEnum::DELETED);
        }
    }
}
