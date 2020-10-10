<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

use Sirius\Orm\Relation\Aggregate;

class LazyAggregate implements LazyLoader
{
    /**
     * @var EntityInterface
     */
    protected $entity;
    /**
     * @var Tracker
     */
    protected $tracker;
    /**
     * @var Aggregate
     */
    protected $aggregate;

    public function __construct(EntityInterface $entity, Tracker $tracker, Aggregate $aggregate)
    {
        $this->entity    = $entity;
        $this->tracker   = $tracker;
        $this->aggregate = $aggregate;
    }

    public function load()
    {
        $results = $this->tracker->getAggregateResults($this->aggregate);
        $this->aggregate->attachAggregateToEntity($this->entity, $results);
    }
}
