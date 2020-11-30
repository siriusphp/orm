<?php
declare(strict_types=1);

namespace Sirius\Orm\Helpers;

class QueryHelper
{
    public static function reference($table, $tableAlias)
    {
        if (! $tableAlias || $table == $tableAlias) {
            return $table;
        }

        return "{$table} as {$tableAlias}";
    }

    public static function joinCondition($firsTable, $firstColumns, $secondTable, $secondColumns)
    {
        $firstColumns  = (array)$firstColumns;
        $secondColumns = (array)$secondColumns;

        $parts = [];
        foreach ($firstColumns as $k => $col) {
            $parts[] = "{$firsTable}.{$col} = {$secondTable}.{$secondColumns[$k]}";
        }

        return implode(' AND ', $parts);
    }
}
