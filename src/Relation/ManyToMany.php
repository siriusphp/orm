<?php

namespace Sirius\Orm\Relation;

use Sirius\Orm\Action\BaseAction;
use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\Entity\Tracker;
use Symfony\Component\Inflector\Inflector;

class ManyToMany extends Relation
{
    protected function applyDefaults(): void
    {
        parent::applyDefaults();

        $this->setOptionIfMissing(RelationOption::THROUGH_COLUMNS_PREFIX, 'pivot_');

        $foreignKey = $this->foreignMapper->getPrimaryKey();
        if (! isset($this->options[RelationOption::FOREIGN_KEY])) {
            $this->options[RelationOption::FOREIGN_KEY] = $foreignKey;
        }

        $nativeKey = $this->foreignMapper->getPrimaryKey();
        if (! isset($this->options[RelationOption::NATIVE_KEY])) {
            $this->options[RelationOption::NATIVE_KEY] = $nativeKey;
        }

        if (! isset($this->options[RelationOption::THROUGH_TABLE])) {
            $tables = [$this->foreignMapper->getTable(), $this->nativeMapper->getTable()];
            sort($tables);
            $this->options[RelationOption::THROUGH_TABLE] = implode('_', $tables);
        }

        if (! isset($this->options[RelationOption::THROUGH_NATIVE_COLUMN])) {
            $prefix = Inflector::singularize($this->nativeMapper->getTableAlias(true));

            $this->options[RelationOption::THROUGH_NATIVE_COLUMN] = $this->getKeyColumn($prefix, $nativeKey);
        }

        if (! isset($this->options[RelationOption::THROUGH_FOREIGN_COLUMN])) {
            $prefix = Inflector::singularize($this->foreignMapper->getTableAlias(true));

            $this->options[RelationOption::THROUGH_FOREIGN_COLUMN] = $this->getKeyColumn($prefix, $foreignKey);
        }
    }

    public function getQuery(Tracker $tracker)
    {
        $nativeKey = $this->options[RelationOption::NATIVE_KEY];
        $nativePks = $tracker->pluck($nativeKey);

        $query = $this->foreignMapper
            ->newQuery();

        $query = $this->joinWithThroughTable($query)
                      ->where($this->options[RelationOption::THROUGH_NATIVE_COLUMN], $nativePks);

        if ($this->getOption(RelationOption::QUERY_CALLBACK) &&
            is_callable($this->getOption(RelationOption::QUERY_CALLBACK))) {
            $callback = $this->options[RelationOption::QUERY_CALLBACK];
            $query    = $callback($query);
        }

        if ($this->getOption(RelationOption::FOREIGN_GUARDS)) {
            $query->setGuards($this->options[RelationOption::FOREIGN_GUARDS]);
        }

        $query = $this->addPivotColumns($query);

        return $query;
    }

    protected function joinWithThroughTable($query)
    {
        $through          = $this->getOption(RelationOption::THROUGH_TABLE);
        $throughAlias     = $this->getOption(RelationOption::THROUGH_TABLE_ALIAS);
        $throughReference = $query->reference($through, $throughAlias);
        $throughName      = $throughAlias ?? $through;

        $foreignTableName       = $this->foreignMapper->getTableAlias(true);
        $throughTableConditions = [];

        foreach ((array)$this->options[RelationOption::FOREIGN_KEY] as $k => $col) {
            $throughCols              = (array)$this->options[RelationOption::THROUGH_FOREIGN_COLUMN];
            $throughCol               = $throughCols[$k];
            $throughTableConditions[] = "{$foreignTableName}.{$col} = {$throughName}.{$throughCol}";
        }

        return $query->join('INNER', $throughReference, implode(' AND ', $throughTableConditions));
    }

    private function addPivotColumns($query)
    {
        $throughColumns = $this->getOption(RelationOption::THROUGH_COLUMNS);

        $through      = $this->getOption(RelationOption::THROUGH_TABLE);
        $throughAlias = $this->getOption(RelationOption::THROUGH_TABLE_ALIAS);
        $throughName  = $throughAlias ?? $through;

        if (! empty($throughColumns)) {
            $prefix = $this->getOption(RelationOption::THROUGH_COLUMNS_PREFIX);
            foreach ($throughColumns as $col) {
                $query->columns("{$throughName}.{$col} AS {$prefix}{$col}");
            }
        }

        foreach ((array)$this->options[RelationOption::THROUGH_NATIVE_COLUMN] as $col) {
            $query->columns("{$throughName}.{$col}");
        }

        return $query;
    }

    protected function computeKeyPairs()
    {
        $pairs      = [];
        $nativeKey  = (array)$this->options[RelationOption::NATIVE_KEY];
        $foreignKey = (array)$this->options[RelationOption::THROUGH_NATIVE_COLUMN];
        foreach ($nativeKey as $k => $v) {
            $pairs[$v] = $foreignKey[$k];
        }

        return $pairs;
    }

    public function attachMatchesToEntity(EntityInterface $nativeEntity, array $result)
    {
        $found = [];
        foreach ($result as $foreignEntity) {
            if ($this->entitiesBelongTogether($nativeEntity, $foreignEntity)) {
                $found[] = $foreignEntity;
            }
        }

        $nativeKey  = (array)$this->getOption(RelationOption::NATIVE_KEY);
        $foreignKey = (array)$this->getOption(RelationOption::FOREIGN_KEY);

        foreach ($nativeKey as $k => $col) {
            $this->nativeMapper->setEntityAttribute(
                $nativeEntity,
                $col,
                new Collection($found)
            );
        }

        $this->nativeMapper->setEntityAttribute($nativeEntity, $this->name, $found);
    }

    protected function attachToDelete(BaseAction $action)
    {
        return;
    }

    protected function attachToSave(BaseAction $action)
    {
        return;
    }
}
