<?php
declare(strict_types=1);

namespace Sirius\Orm\Definition;

use Sirius\Orm\Helpers\Inflector;
use Sirius\Orm\Helpers\Str;

class Mapper extends Base
{
    /**
     * @var Orm
     */
    protected $orm;

    protected $name;

    protected $namespace;

    protected $className;

    protected $destination;

    protected $entityNamespace;

    protected $entityClass;

    protected $entityDestination;

    protected $primaryKey = 'id';

    protected $table;

    protected $tableAlias;

    protected $columns = [];

    protected $defaults = [];

    protected $behaviours = [];

    protected $relations = [];

    protected $computedProperties = [];

    protected $queryScopes = [];


    public function getErrors(): array
    {
        $errors = [];

        if ( ! $this->table) {
            $errors[] = 'Missing table property';
        }

        if ( ! $this->className) {
            $errors[] = 'Missing class name property';
        }

        if ($this->destination) {
            if ( ! is_dir($this->destination)) {
                $errors[] = sprintf('%s is not a valid directory', $this->destination);
            } elseif ( ! is_writable($this->destination)) {
                $errors[] = sprintf('%s is not writable', $this->destination);
            }
        }

        if ( ! $this->entityClass) {
            $errors[] = 'Missing entity class name property';
        }

        if ($this->entityDestination) {
            if ( ! is_dir($this->entityDestination)) {
                $errors[] = sprintf('%s is not a valid directory', $this->entityDestination);
            } elseif ( ! is_writable($this->entityDestination)) {
                $errors[] = sprintf('%s is not writable', $this->entityDestination);
            }
        }

        if ( ! $this->columns || empty($this->columns)) {
            $errors[] = 'Missing columns definitions';
        }

        return $errors;
    }

    /**
     * @return Orm
     */
    public function getOrm()
    {
        return $this->orm;
    }

    /**
     * @param mixed $orm
     *
     * @return Mapper
     */
    public function setOrm(Orm $orm)
    {
        $this->orm = $orm;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param mixed $className
     *
     * @return Mapper
     */
    public function setClassName($className)
    {
        $this->className = $className;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNamespace()
    {
        return $this->namespace ?: $this->orm->getMapperNamespace();
    }

    /**
     * @param mixed $namespace
     *
     * @return Mapper
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDestination()
    {
        return $this->destination ?: $this->orm->getMapperDestination();
    }

    /**
     * @param mixed $destination
     *
     * @return Mapper
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * @param mixed $entityClass
     *
     * @return Mapper
     */
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntityNamespace()
    {
        return $this->entityNamespace ?: $this->orm->getEntityNamespace();
    }

    /**
     * @param mixed $entityNamespace
     *
     * @return Mapper
     */
    public function setEntityNamespace($entityNamespace)
    {
        $this->entityNamespace = $entityNamespace;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntityDestination()
    {
        return $this->entityDestination ?: $this->orm->getEntityDestination();
    }

    /**
     * @param mixed $entityDestination
     *
     * @return Mapper
     */
    public function setEntityDestination($entityDestination)
    {
        $this->entityDestination = $entityDestination;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    /**
     * @param string $primaryKey
     *
     * @return Mapper
     */
    public function setPrimaryKey(string $primaryKey): Mapper
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param mixed $table
     *
     * @return Mapper
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTableAlias()
    {
        return $this->tableAlias;
    }

    /**
     * @param mixed $tableAlias
     *
     * @return Mapper
     */
    public function setTableAlias($tableAlias)
    {
        $this->tableAlias = $tableAlias;

        return $this;
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @param array $columns
     *
     * @return Mapper
     */
    public function setColumns(array $columns): Mapper
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * @return array
     */
    public function getDefaults(): array
    {
        return $this->defaults;
    }

    /**
     * @param array $defaults
     *
     * @return Mapper
     */
    public function setDefaults(array $defaults): Mapper
    {
        $this->defaults = $defaults;

        return $this;
    }

    public function addAutoIncrementColumn($name = 'id')
    {
        return $this->addColumn(
            'id',
            Column::integer(true)
                  ->setAutoIncrement(true)
        );
    }

    public function addColumn($name, Column $column): Mapper
    {
        $column->setName($name);
        $column->setMapper($this);
        $this->columns[$name] = $column;

        return $this;
    }

    public function addBehaviour($name, $behaviour)
    {
        $this->behaviours[$name] = $behaviour;

        return $this;
    }

    public function addRelation($name, Relation $relation)
    {
        $relation->setMapper($this);
        $this->relations[$name] = $relation;

        return $this;
    }

    public function addQueryScope($name, QueryScope $queryScope)
    {
        $queryScope->setMapper($this);
        if (!$queryScope->getName()) {
            $queryScope->setName($name);
        }
        $this->queryScopes[$name] = $queryScope;

        return $this;
    }

    public function addComputedProperty($name, ComputedProperty $property)
    {
        $property->setMapper($this);
        if (!$property->getName()) {
            $property->setName($name);
        }
        $this->computedProperties[$name] = $property;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;

        $singular = Inflector::singularize($name);

        if ( ! $this->table) {
            $this->setTable($name);
        }

        if ($this->table !== $name && ! $this->tableAlias) {
            $this->setTableAlias($name);
        }

        if ( ! $this->className) {
            $this->setClassName(Str::className($singular . 'Mapper'));
        }

        if ( ! $this->entityClass) {
            $this->setEntityClass(Str::className($singular));
        }

        return $this;
    }

}
