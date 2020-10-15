<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

use Sirius\Orm\Contract\LazyLoader;
use Sirius\Orm\Relation\Relation;

class LazyRelation implements LazyLoader
{
    /**
     * @var Tracker
     */
    protected $tracker;
    /**
     * @var Relation
     */
    protected $relation;

    public function __construct(Tracker $tracker, Relation $relation)
    {
        $this->tracker  = $tracker;
        $this->relation = $relation;
    }

    public function getForEntity($entity)
    {
        $results = $this->tracker->getResultsForRelation($this->relation->getOption('name'));
        $this->relation->attachMatchesToEntity($entity, $results);
    }
}
