<?php
declare(strict_types=1);

namespace Sirius\Orm\Blueprint;

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

    protected $mapperMethods = [];

    protected $queryMethods = [];

    protected $entityMethods = [];


    public static function make(string $name)
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

        if (empty($this->columns)) {
            $errors[] = 'Missing columns definitions';
        }

        return $errors;
    }

    public function getObservers(): array
    {
        $observers = [];
        /** @var Column $column */
        foreach ($this->columns as $column) {
            $observers = array_merge_recursive($observers, $column->getObservers());
        }

        /** @var Behaviour $behaviour */
        foreach ($this->behaviours as $behaviour) {
            $observers = array_merge_recursive($observers, $behaviour->getObservers());
        }

        /** @var ComputedProperty $property */
        foreach ($this->computedProperties as $property) {
            $observers = array_merge_recursive($observers, $property->getObservers());
        }

        return $observers;
    }

    public function getOrm(): Orm
    {
        return $this->orm;
    }

    public function setOrm(Orm $orm): Mapper
    {
        $this->orm = $orm;

        return $this;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function setClassName(string $className): Mapper
    {
        $this->className = $className;

        return $this;
    }

    public function getNamespace(): string
    {
        return $this->namespace ?: $this->orm->getMapperNamespace();
    }

    public function setNamespace(string $namespace): Mapper
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function getDestination(): string
    {
        return $this->destination ?: $this->orm->getMapperDestination();
    }

    public function setDestination($destination): Mapper
    {
        $this->destination = $destination;

        return $this;
    }

    public function getEntityClass():string
    {
        return $this->entityClass;
    }

    public function setEntityClass(string $entityClass): Mapper
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    public function getEntityNamespace(): string
    {
        return $this->entityNamespace ?: $this->orm->getEntityNamespace();
    }

    public function setEntityNamespace(string $entityNamespace): Mapper
    {
        $this->entityNamespace = $entityNamespace;

        return $this;
    }

    public function getEntityDestination(): string
    {
        return $this->entityDestination ?: $this->orm->getEntityDestination();
    }

    public function setEntityDestination(string $entityDestination): Mapper
    {
        $this->entityDestination = $entityDestination;

        return $this;
    }

    public function getEntityStyle(): string
    {
        return $this->entityStyle;
    }

    public function setEntityStyle(string $entityStyle): Mapper
    {
        $this->entityStyle = $entityStyle;

        return $this;
    }

    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    public function setPrimaryKey(string $primaryKey): Mapper
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function setTable($table): Mapper
    {
        $this->table = $table;

        return $this;
    }

    public function getTableAlias(): string
    {
        return $this->tableAlias;
    }

    public function setTableAlias($tableAlias): Mapper
    {
        $this->tableAlias = $tableAlias;

        return $this;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function setColumns(array $columns): Mapper
    {
        $this->columns = $columns;

        return $this;
    }

    public function getDefaults(): array
    {
        return $this->defaults;
    }

    public function setDefaults(array $defaults): Mapper
    {
        $this->defaults = $defaults;

        return $this;
    }

    public function addAutoIncrementColumn($name = 'id'): Mapper
    {
        return $this->addColumn(
            Column::integer($name, true)
                  ->setAutoIncrement(true)
        );
    }

    public function getGuards(): array
    {
        return $this->guards;
    }

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

    public function addMethod(ClassMethod $method): Mapper
    {
        $this->mapperMethods[$method->getName()] = $method;

        return $this;
    }

    public function addRelation($name, Relation $relation): Mapper
    {
        $relation->setMapper($this);
        if ( ! $relation->getForeignMapper()) {
            $relation->setForeignMapper(Inflector::pluralize($name));
        }
        $this->relations[$name] = $relation;

        return $this;
    }

    public function getRelations(): array
    {
        return $this->relations;
    }

    public function addComputedProperty(ComputedProperty $property): Mapper
    {
        $property->setMapper($this);
        $this->computedProperties[$property->getName()] = $property;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Mapper
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

    public function getQueryClass()
    {
        return str_replace('Mapper', 'Query', $this->getClassName());
    }
}
