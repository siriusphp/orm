<?php
declare(strict_types=1);

namespace Sirius\Orm\Behaviour;

use Psr\EventDispatcher\EventDispatcherInterface;
use Sirius\Orm\Contract\EntityInterface;
use Sirius\Orm\Event\DeletedEntity;
use Sirius\Orm\Event\DeletingEntity;
use Sirius\Orm\Event\NewEntity;
use Sirius\Orm\Event\NewQuery;
use Sirius\Orm\Event\SavedEntity;
use Sirius\Orm\Event\SavingEntity;
use Sirius\Orm\Mapper;
use Sirius\Orm\Query;

class Events implements BehaviourInterface
{
    protected $mapperName;

    /**
     * @var EventDispatcherInterface
     */
    protected $events;

    public function __construct(EventDispatcherInterface $events, string $mapperName)
    {
        $this->events     = $events;
        $this->mapperName = $mapperName;
    }

    public function getName()
    {
        return 'events';
    }

    public function onNewQuery(/** @scrutinizer ignore-unused */ Mapper $mapper, Query $query)
    {
        $event = new NewQuery($this->mapperName, $query);
        $this->events->dispatch($event);

        return $event->getQuery();
    }

    public function onNewEntity(/** @scrutinizer ignore-unused */ Mapper $mapper, EntityInterface $entity)
    {
        $event = new NewEntity($this->mapperName, $entity);
        $this->events->dispatch($event);

        return $event->getEntity();
    }

    public function onSaving(/** @scrutinizer ignore-unused */ Mapper $mapper, EntityInterface $entity)
    {
        $event = new SavingEntity($this->mapperName, $entity);
        $this->events->dispatch($event);

        return $event->getEntity();
    }

    public function onSaved(/** @scrutinizer ignore-unused */ Mapper $mapper, EntityInterface $entity)
    {
        $event = new SavedEntity($this->mapperName, $entity);
        $this->events->dispatch($event);

        return $event->getEntity();
    }

    public function onDeleting(/** @scrutinizer ignore-unused */ Mapper $mapper, EntityInterface $entity)
    {
        $event = new DeletingEntity($this->mapperName, $entity);
        $this->events->dispatch($event);

        return $event->getEntity();
    }

    public function onDeleted(/** @scrutinizer ignore-unused */ Mapper $mapper, EntityInterface $entity)
    {
        $event = new DeletedEntity($this->mapperName, $entity);
        $this->events->dispatch($event);

        return $event->getEntity();
    }
}
