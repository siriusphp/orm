<?php
declare(strict_types=1);

namespace Sirius\Orm\Action;

use Sirius\Orm\Connection;
use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\Mapper;

class Save extends BaseAction
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
}
