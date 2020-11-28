<?php
declare(strict_types=1);

namespace Sirius\Orm\Blueprint\Relation;

use Sirius\Orm\Blueprint\Mapper;
use Sirius\Orm\Blueprint\Relation;
use Sirius\Orm\Helpers\Inflector;
use Sirius\Orm\Relation\RelationConfig;

class ManyToMany extends Relation
{
    protected $type = RelationConfig::TYPE_MANY_TO_MANY;

    protected $foreignKey = 'id';

    protected $throughTable;

    protected $throughTableAlias;

    protected $throughGuards = [];

    protected $throughColumns = [];

    protected $throughNativeColumn = '';

    protected $throughForeignColumn = '';

    protected $aggregates = [];

    public function setMapper(Mapper $mapper): Relation
    {
        $this->nativeKey = $mapper->getPrimaryKey();
        parent::setMapper($mapper);
        $this->maybeSetAdditionalProperties();

        return $this;
    }

    public function setForeignMapper($foreignMapper)
    {
        parent::setForeignMapper($foreignMapper);
        $this->maybeSetAdditionalProperties();

        return $this;
    }

    protected function maybeSetAdditionalProperties()
    {
        if ( ! $this->mapper || ! $this->foreignMapper) {
            return;
        }

        if ( ! $this->throughTable) {
            $tablePrefix = $this->mapper->getTableAlias() ?
                str_replace($this->mapper->getTable(), '', $this->mapper->getTableAlias())
                : '';

            $tables = [$this->mapper->getTableAlias() ?: $this->mapper->getTable(), $this->foreignMapper];
            sort($tables);

            $this->throughTable = $tablePrefix . implode('_', $tables);

            if ($tablePrefix) {
                $this->throughTableAlias = implode('_', $tables);
            }
        }

        if ( ! $this->throughNativeColumn) {
            $this->throughNativeColumn = Inflector::singularize($this->mapper->getName()) . '_id';
        }

        if ( ! $this->throughForeignColumn) {
            $this->throughForeignColumn = Inflector::singularize($this->foreignMapper) . '_id';
        }
    }

    public function addAggregate($name, $aggregate)
    {
        $this->aggregates[$name] = $aggregate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getThroughTable()
    {
        return $this->throughTable;
    }

    /**
     * @param mixed $throughTable
     *
     * @return ManyToMany
     */
    public function setThroughTable(string $throughTable)
    {
        $this->throughTable = $throughTable;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getThroughTableAlias()
    {
        return $this->throughTableAlias;
    }

    /**
     * @param mixed $throughTableAlias
     *
     * @return ManyToMany
     */
    public function setThroughTableAlias($throughTableAlias)
    {
        $this->throughTableAlias = $throughTableAlias;

        return $this;
    }

    /**
     * @return array
     */
    public function getThroughGuards(): array
    {
        return $this->throughGuards;
    }

    /**
     * @param array $throughGuards
     *
     * @return ManyToMany
     */
    public function setThroughGuards(array $throughGuards): ManyToMany
    {
        $this->throughGuards = $throughGuards;

        return $this;
    }

    /**
     * @return array
     */
    public function getThroughColumns(): array
    {
        return $this->throughColumns;
    }

    /**
     * Pairs of column name (from table) and attribute name (in the linked model)
     *
     * @param array $throughColumns
     *
     * @return ManyToMany
     */
    public function setThroughColumns(array $throughColumns): ManyToMany
    {
        $this->throughColumns = $throughColumns;

        $this->maybeSetAdditionalProperties();

        return $this;
    }

    /**
     * @return string
     */
    public function getThroughNativeColumn(): string
    {
        return $this->throughNativeColumn;
    }

    /**
     * @param string $throughNativeColumn
     *
     * @return ManyToMany
     */
    public function setThroughNativeColumn(string $throughNativeColumn): ManyToMany
    {
        $this->throughNativeColumn = $throughNativeColumn;

        return $this;
    }

    /**
     * @return string
     */
    public function getThroughForeignColumn(): string
    {
        return $this->throughForeignColumn;
    }

    /**
     * @param string $throughForeignColumn
     *
     * @return ManyToMany
     */
    public function setThroughForeignColumn(string $throughForeignColumn): ManyToMany
    {
        $this->throughForeignColumn = $throughForeignColumn;

        return $this;
    }


}
