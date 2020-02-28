<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

use Sirius\Orm\Collection\Collection;
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

    public function __construct(array $rows = [])
    {
        $this->rows   = $rows;
    }

    public function setRelation($name, Relation $relation, $callback, array $nextLoad = [], $overwrite = false)
    {
        if ($overwrite || ! isset($this->relations[$name])) {
            $this->relations[$name]        = $relation;
            $this->relationCallback[$name] = $callback;
            if (!empty($nextLoad)) {
                $this->relationNextLoad[$name] = $nextLoad;
            }
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
        $query         = $aggregate->getQuery($this);

        $results                      = $query->fetchAll();
        $this->aggregateResults[$name] = $results instanceof Collection ? $results->getValues() : $results;

        return $this->aggregateResults[$name];
    }

    public function pluck($columns)
    {
        $result = [];
        foreach ($this->rows as $row) {
            $value = $this->getColumnsFromRow($row, $columns);
            if ($value && !in_array($value, $result)) {
                $result[] = $value;
            }
        }

        return $result;
    }

    protected function getColumnsFromRow($row, $columns)
    {
        if (is_array($columns) && count($columns) > 1) {
            $result = [];
            foreach ($columns as $column) {
                if ($row instanceof GenericEntity) {
                    $result[] = $row->get($column);
                } else {
                    $result[] = $row[$column] ?? null;
                }
            }

            return $result;
        }

        $column = is_array($columns) ? $columns[0] : $columns;

        return $row instanceof GenericEntity ? $row->get($column) : ($row[$column] ?? null);
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
        /** @var Query $query */
        $query = $relation->getQuery($this);

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
