<?php
declare(strict_types=1);

namespace Sirius\Orm\Definition;

use Nette\PhpGenerator\ClassType;
use Sirius\Orm\Helpers\Inflector;
use Sirius\Orm\Helpers\Str;

class Mapper extends Base
{
    const ENTITY_STYLE_PROPERTIES = 'properties';
    const ENTITY_STYLE_METHODS = 'methods';

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

    protected $entityStyle = self::ENTITY_STYLE_PROPERTIES;

    protected $primaryKey = 'id';

    protected $table;

    protected $tableAlias;

    protected $columns = [];

    protected $defaults = [];

    protected $behaviours = [];

    protected $relations = [];

    protected $guards = [];

    protected $traits = [];

    protected $computedProperties = [];

    protected $queryScopes = [];

    public static function make(string $name = null)
    {
        return (new static)->setName($name);
    }

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
    public function getEntityStyle(): string
    {
        return $this->entityStyle;
    }

    /**
     * @param string $entityStyle
     *
     * @return Mapper
     */
    public function setEntityStyle(string $entityStyle): Mapper
    {
        $this->entityStyle = $entityStyle;

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
            Column::integer('id', true)
                  ->setAutoIncrement(true)
        );
    }

    /**
     * @return array
     */
    public function getGuards(): array
    {
        return $this->guards;
    }

    /**
     * @param array $guards
     *
     * @return Mapper
     */
    public function setGuards(array $guards): Mapper
    {
        $this->guards = $guards;

        return $this;
    }

    public function addTrait(string $traitClassName): Mapper
    {
        $this->traits[] = $traitClassName;

        return $this;
    }

    public function getTraits(): array
    {
        return $this->traits;
    }

    public function addColumn(Column $column): Mapper
    {
        $column->setMapper($this);
        $this->columns[$column->getName()] = $column;

        return $this;
    }

    public function addBehaviour(Behaviour $behaviour)
    {
        $behaviour->setMapper($this);
        $this->behaviours[$behaviour->getName()] = $behaviour;

        return $this;
    }

    public function addRelation($name, Relation $relation)
    {
        $relation->setMapper($this);
        if ( ! $relation->getForeignMapper()) {
            $relation->setForeignMapper(Inflector::pluralize($name));
        }
        $this->relations[$name] = $relation;

        return $this;
    }

    public function getRelations() {
        return $this->relations;
    }

    public function addQueryScope($name, QueryScope $queryScope)
    {
        $queryScope->setMapper($this);
        if ( ! $queryScope->getName()) {
            $queryScope->setName($name);
        }
        $this->queryScopes[$name] = $queryScope;

        return $this;
    }

    public function addComputedProperty(ComputedProperty $property)
    {
        $property->setMapper($this);
        $this->computedProperties[$property->getName()] = $property;

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
            $this->setClassName(Str::className($singular) . 'Mapper');
        }

        if ( ! $this->entityClass) {
            $this->setEntityClass(Str::className($singular));
        }

        return $this;
    }

    public function observeMapperConfig(array $config): array
    {
        /** @var Column $column */
        foreach ($this->getColumns() as $column) {
            $config = $column->observeMapperConfig($config);
        }
        /** @var Behaviour $behaviour */
        foreach ($this->behaviours as $behaviour) {
            $config = $behaviour->observeMapperConfig($config);
        }
        /** @var Relation $relation */
        foreach ($this->relations as $relation) {
            $config = $relation->observeMapperConfig($config);
        }

        return parent::observeMapperConfig($config);
    }

    public function observeBaseMapperClass(ClassType $class): ClassType
    {
        /** @var Behaviour $behaviour */
        foreach ($this->behaviours as $behaviour) {
            $class = $behaviour->observeBaseMapperClass($class);
        }
        /** @var Relation $relation */
        foreach ($this->relations as $relation) {
            $class = $relation->observeBaseMapperClass($class);
        }

        return parent::observeBaseMapperClass($class);
    }

    public function observeBaseQueryClass(ClassType $class): ClassType
    {
        /** @var Behaviour $behaviour */
        foreach ($this->behaviours as $behaviour) {
            $class = $behaviour->observeBaseQueryClass($class);
        }
        /** @var Relation $relation */
        foreach ($this->relations as $relation) {
            $class = $relation->observeBaseQueryClass($class);
        }

        return parent::observeBaseMapperClass($class);
    }

    public function observeBaseEntityClass(ClassType $class): ClassType
    {
        /** @var Column $column */
        foreach ($this->getColumns() as $column) {
            $class = $column->observeBaseEntityClass($class);
        }
        /** @var ComputedProperty $column */
        foreach ($this->computedProperties as $property) {
            $class = $property->observeBaseEntityClass($class);
        }
        /** @var Behaviour $behaviour */
        foreach ($this->behaviours as $behaviour) {
            $class = $behaviour->observeBaseEntityClass($class);
        }
        /** @var Relation $relation */
        foreach ($this->relations as $relation) {
            $class = $relation->observeBaseEntityClass($class);
        }

        return parent::observeBaseEntityClass($class);
    }

    public function getQueryClass()
    {
        return str_replace('Mapper', 'Query', $this->getClassName());
    }
}
