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

    protected $pivotTable;

    protected $pivotTableAlias;

    protected $pivotGuards = [];

    protected $pivotColumns = [];

    protected $pivotNativeColumn = '';

    protected $pivotForeignColumn = '';

    protected $aggregates = [];

    public function setMapper(Mapper $mapper): Relation
    {
        $this->nativeKey = $mapper->getPrimaryKey();
        parent::setMapper($mapper);
        $this->maybeSetAdditionalProperties();

        return $this;
    }

    public function setForeignMapper($foreignMapper): Relation
    {
        parent::setForeignMapper($foreignMapper);
        $this->maybeSetAdditionalProperties();

        return $this;
    }

    public function getObservers(): array
    {
        $observer = $this->getObserver()->with($this);

        return [
            $this->getMapper()->getName() . '_base_entity' => [$observer],
            $this->getForeignMapper() . '_base_entity'     => [$observer],
            $this->getForeignMapper() . '_mapper_config'   => [$observer],
        ];
    }

    protected function maybeSetAdditionalProperties()
    {
        if (! $this->mapper || ! $this->foreignMapper) {
            return;
        }

        if (! $this->pivotTable) {
            $tablePrefix = $this->mapper->getTableAlias() ?
                str_replace($this->mapper->getTable(), '', $this->mapper->getTableAlias())
                : '';

            $tables = [$this->mapper->getTableAlias() ?: $this->mapper->getTable(), $this->foreignMapper];
            sort($tables);

            $this->pivotTable = $tablePrefix . implode('_', $tables);

            if ($tablePrefix) {
                $this->pivotTableAlias = implode('_', $tables);
            }
        }

        if (! $this->pivotNativeColumn) {
            $this->pivotNativeColumn = Inflector::singularize($this->mapper->getName()) . '_id';
        }

        if (! $this->pivotForeignColumn) {
            $this->pivotForeignColumn = Inflector::singularize($this->foreignMapper) . '_id';
        }
    }

    public function addAggregate($name, $aggregate)
    {
        $this->aggregates[$name] = $aggregate;

        return $this;
    }

    public function getAggregates(): array
    {
        return $this->aggregates;
    }

    /**
     * @return mixed
     */
    public function getPivotTable()
    {
        return $this->pivotTable;
    }

    /**
     * @param mixed $pivotTable
     *
     * @return ManyToMany
     */
    public function setPivotTable(string $pivotTable)
    {
        $this->pivotTable = $pivotTable;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPivotTableAlias()
    {
        return $this->pivotTableAlias;
    }

    /**
     * @param mixed $pivotTableAlias
     *
     * @return ManyToMany
     */
    public function setPivotTableAlias($pivotTableAlias)
    {
        $this->pivotTableAlias = $pivotTableAlias;

        return $this;
    }

    /**
     * @return array
     */
    public function getPivotGuards(): array
    {
        return $this->pivotGuards;
    }

    /**
     * @param array $pivotGuards
     *
     * @return ManyToMany
     */
    public function setPivotGuards(array $pivotGuards): ManyToMany
    {
        $this->pivotGuards = $pivotGuards;

        return $this;
    }

    /**
     * @return array
     */
    public function getPivotColumns(): array
    {
        return $this->pivotColumns;
    }

    /**
     * Pairs of column name (from table) and attribute name (in the linked model)
     *
     * @param array $pivotColumns
     *
     * @return ManyToMany
     */
    public function setPivotColumns(array $pivotColumns): ManyToMany
    {
        $this->pivotColumns = $pivotColumns;

        $this->maybeSetAdditionalProperties();

        return $this;
    }

    /**
     * @return string
     */
    public function getPivotNativeColumn(): string
    {
        return $this->pivotNativeColumn;
    }

    /**
     * @param string $pivotNativeColumn
     *
     * @return ManyToMany
     */
    public function setPivotNativeColumn(string $pivotNativeColumn): ManyToMany
    {
        $this->pivotNativeColumn = $pivotNativeColumn;

        return $this;
    }

    /**
     * @return string
     */
    public function getPivotForeignColumn(): string
    {
        return $this->pivotForeignColumn;
    }

    /**
     * @param string $pivotForeignColumn
     *
     * @return ManyToMany
     */
    public function setPivotForeignColumn(string $pivotForeignColumn): ManyToMany
    {
        $this->pivotForeignColumn = $pivotForeignColumn;

        return $this;
    }
}
