<?php
declare(strict_types=1);

namespace Sirius\Orm\Helpers;

class QueryHelper
{

    public static function reference($table, $tableAlias)
    {
        if ( ! $tableAlias || $table == $tableAlias) {
            return $table;
        }

        return "{$table} as {$tableAlias}";
    }
}