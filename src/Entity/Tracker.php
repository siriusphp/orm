<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Contract\HydratorInterface;
use Sirius\Orm\Mapper;
use Sirius\Orm\Query;
use Sirius\Orm\Relation\Aggregate;
use Sirius\Orm\Relation\Relation;

class Tracker
{
    protected $rows = [];

    /**
     * @var Mapper
     */
    protected $mapper;

    /**
     * @var array
     */
    protected $relations = [];

    /**
     * @var array
     */
    protected $relationCallback = [];

    /**
     * @var array
     */
    protected $relationNextLoad = [];

    /**
     * @var array
     */
    protected $relationResults = [];

    /**
     * @var array
     */
    protected $aggregateResults = [];

    /**
     * @var array
     */
    protected $lazyAggregates = [];

    /**
     * @var array
     */
    protected $lazyRelations = [];

    public function __construct(array $rows = [])
    {
        $this->rows = $rows;
    }

    public function setRelation($name, Relation $relation, $callback, array $nextLoad = [])
    {
        $this->relations[$name]        = $relation;
        $this->relationCallback[$name] = $callback;
        if (! empty($nextLoad)) {
            $this->relationNextLoad[$name] = $nextLoad;
        }
    }

    public function hasRelation($name)
    {
        return isset($this->relations[$name]);
    }

    public function getResultsForRelation($name)
    {
        if (! isset($this->relations[$name])) {
            return [];
        }

        if (isset($this->relationResults[$name])) {
            return $this->relationResults[$name];
        }

        $results = $this->queryRelation($name);

        $this->relationResults[$name] = $results;

        return $this->relationResults[$name];
    }

    public function getAggregateResults(Aggregate $aggregate)
    {
        $name = $aggregate->getName();

        if (isset($this->aggregateResults[$name])) {
            return $this->aggregateResults[$name];
        }

        /** @var Query $query */
        $query = $aggregate->getQuery($this);

        $results                       = $query->fetchAll();
        $this->aggregateResults[$name] = $results instanceof Collection ? $results->getValues() : $results;

        return $this->aggregateResults[$name];
    }

    public function pluck($columns, HydratorInterface $hydrator)
    {
        $result = [];
        foreach ($this->rows as $row) {
            $value = $this->getColumnsFromRow($row, $columns, $hydrator);
            if ($value && ! in_array($value, $result)) {
                $result[] = $value;
            }
        }

        return $result;
    }

    protected function getColumnsFromRow($row, $columns, HydratorInterface $hydrator)
    {
        if (is_array($columns) && count($columns) > 1) {
            $result = [];
            foreach ($columns as $column) {
                if (is_array($row)) {
                    $result[] = $row[$column] ?? null;
                } else {
                    $result[] = $hydrator->get($row, $column);
                }
            }

            return $result;
        }

        $column = is_array($columns) ? $columns[0] : $columns;

        return is_object($row) ? $hydrator->get($row, $column) : ($row[$column] ?? null);
    }

    public function getLazyAggregate(Aggregate $aggregate)
    {
        $name = $aggregate->getName();
        if (! isset($this->lazyAggregates[$name])) {
            $this->lazyAggregates[$name] = new LazyAggregate($this, $aggregate);
        }

        return $this->lazyAggregates[$name];
    }

    public function getLazyRelation(Relation $relation)
    {
        $name = $relation->getOption('name');
        if (! isset($this->lazyRelations[$name])) {
            $this->lazyRelations[$name] = new LazyRelation($this, $relation);
        }

        return $this->lazyRelations[$name];
    }

    /**
     * After the entities are created we use this method to swap the rows
     * with the actual entities to save some memory since rows can be quite big
     *
     * @param array $entities
     */
    public function replaceRows(array $entities)
    {
        $this->rows = $entities;
    }

    /**
     * @param $name
     *
     * @return array
     */
    protected function queryRelation($name)
    {
        /** @var Relation $relation */
        $relation = $this->relations[$name];
        /** @var Query|null $query */
        $query = $relation->getQuery($this);

        /**
         * query can be null if there are no entities to be retrieved
         * this is when the native keys are `null` in which case
         * there's no need for a query to be constructed and executed
         */
        if (!$query) {
            return [];
        }

        $queryCallback = $this->relationCallback[$name] ?? null;
        if ($queryCallback && is_callable($queryCallback)) {
            $query = $queryCallback($query);
        }

        $queryNextLoad = $this->relationNextLoad[$name] ?? [];
        if ($queryNextLoad && ! empty($queryNextLoad)) {
            foreach ($queryNextLoad as $next => $callback) {
                $query = $query->load([$next => $callback]);
            }
        }

        $results = $query->get();
        $results = $results instanceof Collection ? $results->getValues() : $results;
        $results = $relation->indexQueryResults($results);

        return $results;
    }
}
