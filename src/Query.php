<?php
declare(strict_types=1);

namespace Sirius\Orm;

use Atlas\Pdo\Connection;
use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Collection\PaginatedCollection;
use Sirius\Sql\Select;

class Query extends Select
{

    /**
     * @var Orm
     */
    protected $orm;

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

    public function __construct(Mapper $mapper)
    {
        parent::__construct($mapper->getReadConnection());
        $this->mapper = $mapper;
        $this->from($this->mapper->getTableReference());
        $this->resetColumns();
        $this->columns($this->mapper->getTableAlias(true) . '.*');
    }

    public function load(...$relations): self
    {
        foreach ($relations as $name => $callback) {
            if (is_int($name)) {
                $this->load[$callback] = null;
            } elseif (is_callable($callback)) {
                $this->load[$name] = $callback;
            } else {
                throw new \InvalidArgumentException('Invalid callable for relation');
            }
        }

        return $this;
    }

    public function first()
    {
        $row = $this->fetchOne();

        return $this->mapper->newEntityFromRow($row, $this->load);
    }

    public function get(): Collection
    {
        return $this->mapper->newCollectionFromRows($this->fetchAll(), $this->load);
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

    public function chunk($count, $callback)
    {
    }

    public function count()
    {
        $this->resetOrderBy();
        $this->resetColumns();
        $this->columns('COUNT(*)');

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

    public function setScopes(array $scopes)
    {
        foreach ($scopes as $name => $callback) {
            $this->scopes[$name] = $callback;
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

    public function reference($table, $tableAlias)
    {
        if (! $tableAlias || $table == $tableAlias) {
            return $table;
        }

        return "{$table} as {$tableAlias}";
    }

    public function getStatement(): string
    {
        $this->applyGuards();

        return parent::getStatement();
    }
}
