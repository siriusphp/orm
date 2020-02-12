<?php
declare(strict_types=1);

namespace Sirius\Orm\Relation;

use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\Entity\Tracker;
use Sirius\Orm\LazyLoader;
use Sirius\Orm\Mapper;

abstract class Relation
{
    /**
     * Name of the relation (used to infer defaults)
     * @var
     */
    protected $name;

    /**
     * @var Mapper
     */
    protected $nativeMapper;

    /**
     * @var Mapper
     */
    protected $foreignMapper;

    /**
     * @var array
     */
    protected $options = [];

    public function __construct($name, Mapper $nativeMapper, Mapper $foreignMapper, array $options = [])
    {
        $this->name          = $name;
        $this->nativeMapper  = $nativeMapper;
        $this->foreignMapper = $foreignMapper;
        $this->options       = array_merge($this->getDefaultOptions(), $options);
    }

    protected function getDefaultOptions()
    {
        $defaults = [
            RelationOption::LOAD_STRATEGY => RelationOption::LOAD_LAZY,
            RelationOption::CASCADE       => true,
        ];

        return $defaults;
    }

    public function getOption($name)
    {
        if ($name == 'name') {
            return $this->name;
        }

        return $this->options[$name] ?? null;
    }

    /**
     * Checks if a native entity belongs and a foreign entity belong together according to this relation
     * It verifies if the attributes are properly linked
     *
     * @param EntityInterface $nativeEntity
     * @param EntityInterface $foreignEntity
     *
     * @return mixed
     */
    public function entitiesBelongTogether(EntityInterface $nativeEntity, EntityInterface $foreignEntity)
    {
        $nativeKey  = $this->options[RelationOption::NATIVE_KEY];
        $foreignKey = $this->options[RelationOption::FOREIGN_KEY];

        if (is_array($nativeKey)) {
            foreach ($nativeKey as $k => $column) {
                $nativeKeyValue  = $this->nativeMapper->getEntityAttribute($nativeEntity, $nativeKey[$k]);
                $foreignKeyValue = $this->foreignMapper->getEntityAttribute($foreignEntity, $foreignKey[$k]);
                if ($nativeKeyValue != $foreignKeyValue) {
                    return false;
                }
            }

            return true;
        }

        $nativeKeyValue  = $this->nativeMapper->getEntityAttribute($nativeEntity, $nativeKey);
        $foreignKeyValue = $this->foreignMapper->getEntityAttribute($foreignEntity, $foreignKey);

        return $nativeKeyValue == $foreignKeyValue;
    }

    protected function getKeyColumn($name, $column)
    {
        if (is_array($column)) {
            $keyColumn = [];
            foreach ($column as $col) {
                $keyColumn[] = $name . '_' . $col;
            }

            return $keyColumn;
        }

        return $name . '_' . $column;
    }

    abstract public function attachesMatchesToEntity(EntityInterface $nativeEntity, array $queryResult);

    abstract public function attachLazyValueToEntity(EntityInterface $entity, Tracker $tracker);

    public function getQuery(Tracker $tracker)
    {
        $nativeKey = $this->options[RelationOption::NATIVE_KEY];
        $nativePks = $tracker->pluck($nativeKey);

        $query = $this->foreignMapper
            ->newQuery()
            ->where($this->options[RelationOption::FOREIGN_KEY], $nativePks);

        if ($this->getOption(RelationOption::FOREIGN_GUARDS)) {
            $query->setGuards($this->options[RelationOption::FOREIGN_GUARDS]);
        }

        if ($this->getOption(RelationOption::QUERY_CALLBACK) &&
            is_callable($this->getOption(RelationOption::QUERY_CALLBACK))) {
            $callback = $this->options[RelationOption::QUERY_CALLBACK];
            $query    = $callback($query);
        }

        return $query;
    }

    public function getLazyLoader(Tracker $tracker, callable $callback = null)
    {
        return new LazyLoader($tracker, $this->nativeMapper, $this, $callback);
    }
}
