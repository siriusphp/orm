<?php
declare(strict_types=1);

namespace Sirius\Orm\Loaders;

use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\Entity\Tracker;
use Sirius\Orm\Mapper;
use Sirius\Orm\Relation\Relation;

class LoaderFactory
{

    /**
     * @var Tracker
     */
    protected $tracker;

    /**
     * @var Relation
     */
    protected $relation;

    /**
     * @var callable
     */
    protected $queryCallback;

    public function __construct(
        Tracker $tracker,
        Mapper $mapper,
        Relation $relation,
        callable $queryCallback = null, // received from the load()
        array $nextLoad = []
    ) {
        $this->tracker       = $tracker;
        $this->relation      = $relation;
        $this->queryCallback = $queryCallback;
        $this->nextLoad      = $nextLoad;
    }

    protected function loadResults()
    {
        $relationName = $this->relation->getOption('name');
        if (! $this->tracker->hasQueryResults($relationName)) {
            $query = $this->relation->getQuery($this->tracker);
            if ($this->queryCallback) {
                $query = $this->queryCallback($query);
            }
            if ($this->nextLoad) {
                $query->load(...$this->nextLoad);
            }

            $this->tracker->setQueryResults($relationName, $query->get());
        }

        return $this->tracker->getQueryResults($relationName);
    }

    public function getEagerForEntity(EntityInterface $entity)
    {
    }

    public function getLazyForEntity(EntityInterface $entity)
    {
    }
}
