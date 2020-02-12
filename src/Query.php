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

    public $guards = [];

    public function __construct(Orm $orm, Mapper $mapper, Connection $connection)
    {
        parent::__construct($connection);
        $this->orm    = $orm;
        $this->mapper = $mapper;
        $this->from($this->reference($this->mapper->getTable(), $this->mapper->getTableAlias()));
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
        $countQuery->resetOrderBy();
        $countQuery->resetColumns();
        $countQuery->columns('COUNT(*)');
        $total = (int)$countQuery->fetchValue();

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

    protected function reference($table, $tableAlias)
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
