<?php
declare(strict_types=1);

namespace Sirius\Orm\Event;

use League\Event\HasEventName;
use Sirius\Orm\Contract\EntityInterface;
use Sirius\Orm\Query;

class DeletedEntity implements HasEventName
{

    /**
     * @var string
     */
    private $mapper;

    /**
     * @var EntityInterface
     */
    private $entity;

    public function __construct(string $mapper, EntityInterface $entity)
    {
        $this->mapper = $mapper;
        $this->entity = $entity;
    }

    public function getEntity(): EntityInterface
    {
        return $this->entity;
    }

    public function setEntity(EntityInterface $entity)
    {
        $this->entity = $entity;
    }

    public function eventName(): string
    {
        return $this->mapper . '.deleted';
    }
}
