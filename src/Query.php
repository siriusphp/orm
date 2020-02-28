<?php
declare(strict_types=1);

namespace Sirius\Orm;

use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Collection\PaginatedCollection;
use Sirius\Sql\Bindings;
use Sirius\Sql\Select;

class Query extends Select
{
    /**
     * @var Mapper
     */
    protected $mapper;

    /**
     * @var array
     */
    protected $load = [];

    /**
     * @var array
     */
    protected $guards = [];

    /**
     * @var array
     */
    protected $scopes = [];

    public function __construct(Mapper $mapper, Bindings $bindings = null, string $indent = '')
    {
        parent::__construct($mapper->getReadConnection(), $bindings, $indent);
        $this->mapper = $mapper;
        $this->from($this->mapper->getTableReference());
        $this->resetColumns();
        $this->columns($this->mapper->getTableAlias(true) . '.*');
    }

    public function __call(string $method, array $params)
    {
        $scope = $this->mapper->getQueryScope($method);
        if ($scope && is_callable($scope)) {
            return $scope($this, ...$params);
        }

        return parent::__call($method, $params);
    }

    public function __clone()
    {
        $vars = get_object_vars($this);
        unset($vars['mapper']);
        foreach ($vars as $name => $prop) {
            if (is_object($prop)) {
                $this->$name = clone $prop;
            }
        }
    }


    public function load(...$relations): self
    {
        foreach ($relations as $relation) {
            if (is_array($relation)) {
                $name     = key($relation);
                $callback = current($relation);
            } else {
                $name     = $relation;
                $callback = null;
            }
            $this->load[$name] = $callback;
        }

        return $this;
    }

    public function joinWith($name): Query
    {
        if (! $this->mapper->hasRelation($name)) {
            throw new \InvalidArgumentException(
                sprintf("Relation %s, not defined for %s", $name, $this->mapper->getTable())
            );
        }
        $relation = $this->mapper->getRelation($name);

        return $relation->joinSubselect($this, $name);
    }

    public function subSelectForJoinWith(): Query
    {
        $subselect = new Query($this->mapper, $this->bindings, $this->indent . '    ');
        $subselect->resetFrom();
        $subselect->resetColumns();

        return $subselect;
    }

    public function first()
    {
        $row = $this->fetchOne();

        return $this->mapper->newEntityFromRow($row, $this->load);
    }

    public function get(): Collection
    {
        return $this->mapper->newCollectionFromRows(
            $this->connection->fetchAll($this->getStatement(), $this->getBindValues()),
            $this->load
        );
    }

    public function paginate($perPage, $page): PaginatedCollection
    {
        /** @var Query $countQuery */
        $countQuery = clone $this;
        $total      = $countQuery->count();

        if ($total == 0) {
            $this->mapper->newPaginatedCollectionFromRows([], $total, $perPage, $page, $this->load);
        }

        $this->perPage($perPage);
        $this->page($page);

        return $this->mapper->newPaginatedCollectionFromRows($this->fetchAll(), $total, $perPage, $page, $this->load);
    }

    /**
     * Executes the query with a limit of $size and applies the callback on each entity
     * The callback can change the DB in such a way that you can end up in an infinite loop
     * (depending on the sorting) so we set a limit on the number of chunks that can be processed
     *
     * @param int $size
     * @param callable $callback
     * @param int $limit
     */
    public function chunk(int $size, callable $callback, int $limit = 100000)
    {
        if (!$this->orderBy->build()) {
            $this->orderBy(...(array) $this->mapper->getPrimaryKey());
        }

        $run = 0;
        while ($run < $limit) {
            $query = clone $this;
            $query->limit($size);
            $query->offset($run * $size);

            $results = $query->get();

            if (count($results) === 0) {
                break;
            }

            foreach ($results as $entity) {
                $callback($entity);
            }

            $run++;
        }
    }

    public function count()
    {
        $this->resetOrderBy();
        $this->resetColumns();
        $this->columns('COUNT(*) AS total');

        return (int)$this->fetchValue();
    }

    public function setGuards(array $guards)
    {
        foreach ($guards as $column => $value) {
            if (is_int($column)) {
                $this->guards[] = $value;
            } else {
                $this->guards[$column] = $value;
            }
        }

        return $this;
    }

    public function resetGuards()
    {
        $this->guards = [];

        return;
    }

    protected function applyGuards()
    {
        if (empty($this->guards)) {
            return;
        }

        $this->groupCurrentWhere();
        foreach ($this->guards as $column => $value) {
            if (is_int($column)) {
                $this->where($value);
            } else {
                $this->where($column, $value);
            }
        }
    }

    public function getStatement(): string
    {
        $this->applyGuards();

        return parent::getStatement();
    }
}
