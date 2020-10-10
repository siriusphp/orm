<?php
declare(strict_types=1);

namespace Sirius\Orm;

use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Collection\PaginatedCollection;
use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\Entity\StateEnum;
use Sirius\Orm\Entity\Tracker;
use Sirius\Orm\Helpers\Arr;
use Sirius\Orm\Relation\Aggregate;
use Sirius\Sql\Bindings;
use Sirius\Sql\Select;

class Query extends Select
{
    /**
     * @var Mapper
     */
    protected $mapper;

    /**
     * @var MapperConfig
     */
    protected $mapperConfig;

    /**
     * @var Connection
     */
    protected $connection;

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

    public function __construct(Connection $connection, Mapper $mapper, Bindings $bindings = null, string $indent = '')
    {
        parent::__construct($connection, $bindings, $indent);
        $this->mapper       = $mapper;
        $this->mapperConfig = $mapper->getConfig();

        $this->from($this->mapperConfig->getTableReference());
        $this->resetColumns();
        $this->columns($this->mapperConfig->getTableAlias(true) . '.*');
    }

    public function __call(string $method, array $params)
    {
        $scope = $this->mapperConfig->getQueryScope($method);
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
        if ( ! $this->mapper->hasRelation($name)) {
            throw new \InvalidArgumentException(
                sprintf("Relation %s, not defined for %s", $name, $this->mapper->getConfig()->getTable())
            );
        }
        $relation = $this->mapper->getRelation($name);

        return $relation->joinSubselect($this, $name);
    }

    public function subSelectForJoinWith(): Query
    {
        $subselect = new static($this->connection, $this->mapper, $this->bindings, $this->indent . '    ');
        $subselect->resetFrom();
        $subselect->resetColumns();

        return $subselect;
    }

    public function first()
    {
        $row = $this->fetchOne();

        return $this->newEntityFromRow($row, $this->load);
    }

    public function get(): Collection
    {
        return $this->newCollectionFromRows(
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
            $this->newPaginatedCollectionFromRows([], $total, $perPage, $page, $this->load);
        }

        $this->perPage($perPage);
        $this->page($page);

        return $this->newPaginatedCollectionFromRows($this->fetchAll(), $total, $perPage, $page, $this->load);
    }

    protected function newEntityFromRow(array $data = null, array $load = [], Tracker $tracker = null)
    {
        if ($data == null) {
            return null;
        }

        $receivedTracker = ! ! $tracker;
        if ( ! $tracker) {
            $receivedTracker = false;
            $tracker         = new Tracker([$data]);
        }

        $entity = $this->mapper->newEntity($data);
        $this->injectRelations($entity, $tracker, $load);
        $this->injectAggregates($entity, $tracker, $load);
        $entity->setState(StateEnum::SYNCHRONIZED);

        if ( ! $receivedTracker) {
            $tracker->replaceRows([$entity]);
        }

        return $entity;
    }

    protected function newCollectionFromRows(array $rows, array $load = []): Collection
    {
        $entities = [];
        $tracker  = new Tracker($rows);
        foreach ($rows as $row) {
            $entity     = $this->newEntityFromRow($row, $load, $tracker);
            $entities[] = $entity;
        }
        $tracker->replaceRows($entities);

        return new Collection($entities);
    }

    protected function newPaginatedCollectionFromRows(
        array $rows,
        int $totalCount,
        int $perPage,
        int $currentPage,
        array $load = []
    ): PaginatedCollection {
        $entities = [];
        $tracker  = new Tracker($rows);
        foreach ($rows as $row) {
            $entity     = $this->newEntityFromRow($row, $load, $tracker);
            $entities[] = $entity;
        }
        $tracker->replaceRows($entities);

        return new PaginatedCollection($entities, $totalCount, $perPage, $currentPage);
    }

    protected function injectRelations(EntityInterface $entity, Tracker $tracker, array $eagerLoad = [])
    {
        foreach (array_keys($this->mapper->getRelations()) as $name) {
            $relation      = $this->mapper->getRelation($name);
            $queryCallback = $eagerLoad[$name] ?? null;
            $nextLoad      = Arr::getChildren($eagerLoad, $name);

            if ( ! $tracker->hasRelation($name)) {
                $tracker->setRelation($name, $relation, $queryCallback, $nextLoad);
            }

            if (array_key_exists($name, $eagerLoad) || in_array($name, $eagerLoad) || $relation->isEagerLoad()) {
                $relation->attachMatchesToEntity($entity, $tracker->getResultsForRelation($name));
            } elseif ($relation->isLazyLoad()) {
                $relation->attachLazyRelationToEntity($entity, $tracker);
            }
        }
    }

    protected function injectAggregates(EntityInterface $entity, Tracker $tracker, array $eagerLoad = [])
    {
        foreach (array_keys($this->mapper->getRelations()) as $name) {
            $relation = $this->mapper->getRelation($name);
            if ( ! method_exists($relation, 'getAggregates')) {
                continue;
            }
            $aggregates = $relation->getAggregates();
            foreach ($aggregates as $aggName => $aggregate) {
                /** @var $aggregate Aggregate */
                if (array_key_exists($aggName, $eagerLoad) || $aggregate->isEagerLoad()) {
                    $aggregate->attachAggregateToEntity($entity, $tracker->getAggregateResults($aggregate));
                } elseif ($aggregate->isLazyLoad()) {
                    $aggregate->attachLazyAggregateToEntity($entity, $tracker);
                }
            }
        }
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
        if ( ! $this->orderBy->build()) {
            $this->orderBy(...(array)$this->mapper->getConfig()->getPrimaryKey());
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

        return $this;
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
