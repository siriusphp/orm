<?php
declare(strict_types=1);

namespace Sirius\Orm\Relation;

final class RelationOption
{
    // general options
    const NAME = 'name';
    const TYPE = 'type';
    const NATIVE_KEY = 'native_key';
    const FOREIGN_KEY = 'foreign_key';
    const FOREIGN_MAPPER = 'foreign_mapper';
    const FOREIGN_GUARDS = 'foreign_guards';
    const LOAD_STRATEGY = 'load_strategy';
    const CASCADE = 'cascade';
    const LOCKED_FIELDS = 'locked_fields';
    const QUERY_CALLBACK = 'query_callback';

    // through options
    const THROUGH_TABLE = 'through_table';
    const THROUGH_TABLE_ALIAS = 'through_table_alias';
    const THROUGH_GUARDS = 'through_guards';
    const THROUGH_COLUMNS = 'through_columns';
    const THROUGH_MAPPER = 'through_mapper';
    const THROUGH_NATIVE_COLUMN = 'through_native_column';
    const THROUGH_FOREIGN_COLUMN = 'through_foreign_column';

    // setters and getters
    const NATIVE_SETTER = 'native_setter';
    const NATIVE_GETTER = 'native_getter';
    const FOREIGN_SETTER = 'foreign_setter';
    const FOREIGN_GETTER = 'foreign_getter';

    // loading option values
    const LOAD_LAZY = 'lazy';
    const LOAD_EAGER = 'eager';

    // types
    const TYPE_ONE_TO_ONE = 'one_to_one';
    const TYPE_ONE_TO_MANY = 'one_to_many';
    const TYPE_MANY_TO_ONE = 'many_to_one';
    const TYPE_MANY_TO_MANY = 'many_to_many';
}
