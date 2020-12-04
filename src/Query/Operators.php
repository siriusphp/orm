<?php
declare(strict_types=1);

namespace Sirius\Orm\Query;

class Operators
{
    protected static $map = [
        'lt' => '<',
        'lte' => '<=',
        'less_than' => '<',
        'less_or_equal' => '<=',
        'gte' => '>=',
        'gt' => '>',
        'greater_or_equal' => '>=',
        'greater_than' => '>=',
        'starts_with' => 'LIKE',
        'ends_with' => 'LIKE',
        'contains' => 'LIKE',
    ];

    public static function getOperatorAndValue($operator, $value)
    {
        if (is_int($operator) || $operator === 'IN' || $operator === '=') {
            $value = strpos($value, ',') ? explode(',', $value) : $value;
            $operator = is_array($value) ? 'IN' : '=';
            return [$operator, $value];
        }

        if ($operator === 'starts_with') {
            $value = $value . '%';
        }

        if ($operator === 'ends_with') {
            $value = '%' . $value;
        }

        if ($operator === 'contains') {
            $value = '%' . $value . '%';
        }

        $operator = static::$map[$operator] ?? $operator;
        return [$operator, $value];
    }
}
