<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

use Sirius\Orm\Contract\EntityInterface;
use Sirius\Orm\Relation\Aggregate;

class LazyAggregate implements LazyLoader
{
    /**
     * @var Tracker
     */
    protected $tracker;
    /**
     * @var Aggregate
     */
    protected $aggregate;

    public function __construct(Tracker $tracker, Aggregate $aggregate)
    {
        $this->tracker   = $tracker;
        $this->aggregate = $aggregate;
    }

    public function load($entity)
    {
        $results = $this->tracker->getAggregateResults($this->aggregate);
        $this->aggregate->attachAggregateToEntity($entity, $results);
    }
}
