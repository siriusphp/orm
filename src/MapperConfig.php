<?php
declare(strict_types=1);

namespace Sirius\Orm;

use Sirius\Orm\Entity\GenericEntity;

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
    const DEFAULT_ATTRIBUTES = 'entityDefaultAttributes';
    const ENTITY_FACTORY = 'entityFactory';
    const BEHAVIOURS = 'behaviours';
    const RELATIONS = 'relations';
    const SCOPES = 'scopes';
    const GUARDS = 'guards';

    public $entityClass = GenericEntity::class;

    public $primaryKey = 'id';

    /**
     * @var string
     */
    public $table;

    /**
     * Used in queries like so: FROM table as tableAlias
     * @var string
     */
    public $tableAlias;

    /**
     * Table columns
     * @var array
     */
    public $columns = [];

    /**
     * Column aliases (table column => entity attribute)
     * @var array
     */
    public $columnAttributeMap = [];

    /**
     * @var null|FactoryInterface
     */
    public $entityFactory = null;

    /**
     * Default attributes
     * @var array
     */
    public $entityDefaultAttributes = [];

    /**
     * List of behaviours to be attached to the mapper
     * @var array[BehaviourInterface]
     */
    public $behaviours = [];

    /**
     * List of relations of the configured mapper
     * (key = name of relation, value = relation instance)
     * @var array[BaseRelation]
     */
    public $relations = [];

    /**
     * List of query callbacks that can be called directly from the query
     * @var array
     */
    public $scopes = [];

    /**
     * List of column-value pairs that act as global filters
     * @var array
     */
    public $guards = [];

    public static function fromArray(array $array)
    {
        $instance = new self;
        foreach ($array as $k => $v) {
            $instance->{$k} = $v;
        }

        return $instance;
    }
}
