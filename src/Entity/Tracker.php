<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Mapper;
use Sirius\Orm\Query;
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
    protected $relationCallbacks = [];

    /**
     * @var array
     */
    protected $relationResults = [];

    public function __construct(Mapper $mapper, array $rows = [])
    {
        $this->mapper = $mapper;
        $this->rows   = $rows;
    }

    public function setRelation($name, Relation $relation, $callback, $overwrite = false)
    {
        if ($overwrite || ! isset($this->relations[$name])) {
            $this->relations[$name]         = $relation;
            $this->relationCallbacks[$name] = $callback;
        }
    }

    public function getRelationResults($name)
    {
        if (! isset($this->relations[$name])) {
            return null;
        }

        if (isset($this->relationResults[$name])) {
            return $this->relationResults[$name];
        }

        /** @var Query $query */
        $query         = $this->relations[$name]->getQuery($this);
        $queryCallback = $this->relationCallbacks[$name] ?? null;
        if ($queryCallback && is_callable($queryCallback)) {
            $query = $queryCallback($query);
        }

        $results                      = $query->get();
        $this->relationResults[$name] = $results instanceof Collection ? $results->getValues() : $results;

        return $this->relationResults[$name];
    }

    public function pluck($columns)
    {
        $result = [];
        foreach ($this->rows as $row) {
            $result[] = $this->getColumnsFromRow($row, $columns);
        }

        return $result;
    }

    protected function getColumnsFromRow($row, $columns)
    {
        if (is_array($columns) && count($columns) > 1) {
            $result = [];
            foreach ($columns as $column) {
                $result[] = $row[$column] ?? null;
            }

            return $result;
        }

        $column = is_array($columns) ? $columns[0] : $columns;

        return $row[$column] ?? null;
    }
}
