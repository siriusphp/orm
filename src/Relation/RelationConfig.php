<?php
declare(strict_types=1);

namespace Sirius\Orm\Relation;

final class RelationConfig
{
    // general options
    const TYPE = 'type';
    const NATIVE_KEY = 'native_key'; // linked columns in the native mapper
    const FOREIGN_KEY = 'foreign_key'; // linked columns in the foreign mapper
    const FOREIGN_MAPPER = 'foreign_mapper';
    const FOREIGN_GUARDS = 'foreign_guards'; // fixed fields in the foreign mapper
    const LOAD_STRATEGY = 'load_strategy';
    const CASCADE = 'cascade';
    const QUERY_CALLBACK = 'query_callback';
    const AGGREGATES = 'aggregates';

    // through options
    const PIVOT_TABLE = 'pivot_table';
    const PIVOT_TABLE_ALIAS = 'pivot_table_alias';
    const PIVOT_GUARDS = 'pivot_guards';
    const PIVOT_COLUMNS = 'pivot_columns'; // column/attribute pairs from the PIVOT_TABLE
    const PIVOT_NATIVE_COLUMN = 'pivot_native_column';
    const PIVOT_FOREIGN_COLUMN = 'pivot_foreign_column';

    // loading option values
    const LOAD_LAZY = 'lazy';
    const LOAD_EAGER = 'eager';
    const LOAD_NONE = 'none';

    // types
    const TYPE_ONE_TO_ONE = 'one_to_one';
    const TYPE_ONE_TO_MANY = 'one_to_many';
    const TYPE_MANY_TO_ONE = 'many_to_one';
    const TYPE_MANY_TO_MANY = 'many_to_many';

    // AGGREGATES
    const AGG_FUNCTION = 'function';
    const AGG_CALLBACK = 'callback';
}
