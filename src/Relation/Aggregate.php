<?php
declare(strict_types=1);

namespace Sirius\Orm\Relation;

use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\Entity\LazyAggregate;
use Sirius\Orm\Entity\Tracker;
use Sirius\Orm\Query;

class Aggregate
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var Relation
     */
    protected $relation;
    /**
     * @var array
     */
    protected $options;

    public function __construct(string $name, Relation $relation, array $options)
    {
        $this->name     = $name;
        $this->relation = $relation;
        $this->options  = $options;
    }

    public function getQuery(Tracker $tracker)
    {
        $keys = $this->relation->getKeyPairs();

        /** @var Query $query */
        $query = $this->relation->getQuery($tracker);
        $query->resetColumns();
        $query->columns(...array_values($keys));
        $query->columns(sprintf(
            '%s as %s',
            $this->options[RelationConfig::AGG_FUNCTION],
            $this->name
        ));

        $callback = $this->options[RelationConfig::AGG_CALLBACK] ?? null;
        if (is_callable($callback)) {
            $query = $callback($query);
        }

        $query->groupBy(...array_values($keys));

        return $query;
    }

    public function attachLazyAggregateToEntity(EntityInterface $entity, Tracker $tracker)
    {
        $valueLoader = new LazyAggregate($entity, $tracker, $this);
        $this->getNativeEntityHydrator()->set($entity, $this->name, $valueLoader);
    }

    public function attachAggregateToEntity(EntityInterface $entity, array $results)
    {
        $found = null;
        foreach ($results as $row) {
            if ($this->entityMatchesRow($entity, $row)) {
                $found = $row;
                break;
            }
        }
        $this->getNativeEntityHydrator()
             ->set($entity, $this->name, $found ? $found[$this->name] : null);
    }

    public function isLazyLoad()
    {
        return ! isset($this->options[RelationConfig::LOAD_STRATEGY]) ||
               $this->options[RelationConfig::LOAD_STRATEGY] == RelationConfig::LOAD_LAZY;
    }

    public function isEagerLoad()
    {
        return isset($this->options[RelationConfig::LOAD_STRATEGY]) &&
               $this->options[RelationConfig::LOAD_STRATEGY] == RelationConfig::LOAD_EAGER;
    }

    public function getName()
    {
        return $this->name;
    }

    private function entityMatchesRow(EntityInterface $entity, $row)
    {
        $keys = $this->relation->getKeyPairs();
        foreach ($keys as $nativeCol => $foreignCol) {
            $entityValue = $this->getNativeEntityHydrator()->get($entity, $nativeCol);
            $rowValue    = $row[$foreignCol];
            // if both native and foreign key values are present (not unlinked entities) they must be the same
            // otherwise we assume that the entities can be linked together
            if ($entityValue && $rowValue && $entityValue != $rowValue) {
                return false;
            }
        }

        return true;
    }

    protected function getNativeEntityHydrator()
    {
        return $this->relation->getNativeMapper()->getConfig()->getEntityHydrator();
    }

    protected function getForeignEntityHydrator()
    {
        return $this->relation->getForeignMapper()->getConfig()->getEntityHydrator();
    }
}
