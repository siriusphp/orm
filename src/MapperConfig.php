<?php
declare(strict_types=1);

namespace Sirius\Orm;

use Sirius\Orm\Behaviour\BehaviourInterface;
use Sirius\Orm\Contract\HydratorInterface;
use Sirius\Orm\Entity\GenericEntity;
use Sirius\Orm\Entity\GenericHydrator;
use Sirius\Orm\Helpers\QueryHelper;
use Sirius\Orm\Relation\Relation;

/**
 * Class MapperConfig
 * Used to create mapper definitions that can be created using Mapper::make($mapperConfigInstance)
 * This is useful for dynamically generated mappers (think Wordpress custom post types)
 * @package Sirius\Orm
 */
class MapperConfig
{
    const ENTITY_CLASS = 'entityClass';
    const PRIMARY_KEY = 'primaryKey';
    const TABLE = 'table';
    const TABLE_ALIAS = 'tableAlias';
    const COLUMNS = 'columns';
    const COLUMN_ATTRIBUTE_MAP = 'columnAttributeMap';
    const CASTS = 'casts';
    const DEFAULT_ATTRIBUTES = 'defaultEntityAttributes';
    const ENTITY_HYDRATOR = 'entityHydrator';
    const BEHAVIOURS = 'behaviours';
    const RELATIONS = 'relations';
    const SCOPES = 'queryScopes';
    const GUARDS = 'guards';

    /**
     * @var string
     */
    protected $entityClass = GenericEntity::class;

    /**
     * @var string|array
     */
    protected $primaryKey = 'id';

    /**
     * @var string
     */
    protected $table;

    /**
     * Used in queries like so: FROM table as tableAlias
     * @var string
     */
    protected $tableAlias;

    /**
     * @var
     */
    protected $tableReference;

    /**
     * Table columns
     * @var array
     */
    protected $columns = [];

    /**
     * Columns casts
     * @var array
     */
    protected $casts = [];

    /**
     * Column aliases (table column => entity attribute)
     * @var array
     */
    protected $columnAttributeMap = [];

    /**
     * Default attributes
     * @var array
     */
    protected $defaultEntityAttributes = [];

    /**
     * List of behaviours to be attached to the mapper
     * @var array[BehaviourInterface]
     */
    protected $behaviours = [];

    /**
     * List of relations of the configured mapper
     * (key = name of relation, value = relation instance)
     * @var array|Relation[]
     */
    protected $relations = [];

    /**
     * Query scopes are functions that can be called on the mapper queries
     * Not recommended, but useful for runtime generated mappers
     * @var array
     */
    protected $queryScopes = [];

    /**
     * List of column-value pairs that act as global filters
     * @var array
     */
    protected $guards = [];

    public static function fromArray(array $array)
    {
        $instance = new self;
        foreach ($array as $k => $v) {
            $instance->{$k} = $v;
        }

        return $instance;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @return string|array
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param bool $fallbackToTable
     *
     * @return string
     */
    public function getTableAlias($fallbackToTable = false)
    {
        return ( ! $this->tableAlias && $fallbackToTable) ? $this->table : $this->tableAlias;
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return array
     */
    public function getCasts(): array
    {
        return $this->casts;
    }

    /**
     * @return array
     */
    public function getColumnAttributeMap(): array
    {
        return $this->columnAttributeMap;
    }


    /**
     * @return array
     */
    public function getDefaultEntityAttributes(): array
    {
        return $this->defaultEntityAttributes;
    }

    /**
     * @return BehaviourInterface[]
     */
    public function getBehaviours(): array
    {
        return $this->behaviours;
    }

    /**
     * @return array|Relation[]
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * @return array
     */
    public function getQueryScopes(): array
    {
        return $this->queryScopes;
    }

    /**
     * @return array
     */
    public function getGuards(): array
    {
        return $this->guards;
    }

    public function getTableReference()
    {
        if ( ! $this->tableReference) {
            $this->tableReference = QueryHelper::reference($this->table, $this->tableAlias);
        }

        return $this->tableReference;
    }

    public function addQueryScope($scope, callable $callback)
    {
        $this->queryScopes[$scope] = $callback;
    }
}
