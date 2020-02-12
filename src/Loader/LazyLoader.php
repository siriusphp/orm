<?php
declare(strict_types=1);

namespace Sirius\Orm\Loaders;

use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\Mapper;
use Sirius\Orm\Query;
use Sirius\Orm\Relation\Relation;

class LazyLoader
{

    /**
     * @var bool
     */
    protected $isLoaded = false;

    protected $result = [];

    /**
     * @var Storage
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
        Storage $tracker,
        Mapper $mapper,
        Relation $relation,
        callable $queryCallback = null // received from the load() methods
    ) {
        $this->tracker       = $tracker;
        $this->relation      = $relation;
        $this->queryCallback = $queryCallback;
    }

    public function load()
    {
        if ($this->isLoaded) {
            return $this->results;
        }

        /** @var Query $query */
        $query = $this->relation->getQuery($this->tracker);

        if (is_callable($this->queryCallback)) {
            $query = $this->queryCallback($query);
        }

        $results = $query->get()->getValues();
    }

    public function getForEntity(EntityInterface $entity)
    {
        $related = [];
        foreach ($this->results as $relatedEntity) {
            if ($this->relation->entitiesBelongTogether($entity, $relatedEntity)) {
                $related[] = $relatedEntity;
            }
        }

        return new Collection($related);
    }
}
