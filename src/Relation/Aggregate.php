<?php
declare(strict_types=1);

namespace Sirius\Orm\Relation;

use Sirius\Orm\Contract\EntityInterface;
use Sirius\Orm\Contract\HydratorInterface;
use Sirius\Orm\Entity\LazyAggregate;
use Sirius\Orm\Entity\LazyValue;
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

    /**
     * @var HydratorInterface
     */
    protected $entityHydrator;

    /**
     * @var HydratorInterface
     */
    protected $foreignEntityHydrator;

    public function __construct(string $name, Relation $relation, array $options)
    {
        $this->name           = $name;
        $this->relation       = $relation;
        $this->options        = $options;
        $this->entityHydrator = $relation->getNativeMapper()->getHydrator();
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

        /**
         * the query callback for the relation or for the aggregate might implement ORDER_BY
         * and that would cause issues with MySQL's sql_mode=ONLY_FULL_GROUP_BY
         */
        $query->resetOrderBy();

        /**
         * just in case a query callback is setting limits
         */
        $query->resetLimit();

        return $query;
    }

    public function attachLazyAggregateToEntity(EntityInterface $entity, Tracker $tracker)
    {
        $valueLoader = $tracker->getLazyAggregate($this);
        $this->entityHydrator->set($entity, $this->name, $valueLoader);
    }

    public function attachAggregateToEntity(EntityInterface $entity, array $results)
    {
        $value = null;
        foreach ($results as $row) {
            if ($this->entityMatchesRow($entity, $row)) {
                $value = $row[$this->name] ?? null;
            }
        }
        // we have to do this because some properties may be read only (ie: there is no setter)
        // using a LazyValue is the only way for the hydrator to inject a value
        $this->entityHydrator->set($entity, $this->name, new LazyValue($value));
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
            $entityValue = $this->entityHydrator->get($entity, $nativeCol);
            $rowValue    = $row[$foreignCol];
            // if both native and foreign key values are present (not unlinked entities) they must be the same
            // otherwise we assume that the entities can be linked together
            if ($entityValue && $rowValue && $entityValue != $rowValue) {
                return false;
            }
        }

        return true;
    }
}
