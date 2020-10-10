<?php
declare(strict_types=1);

namespace Sirius\Orm\Helpers;

class Str
{
    protected static $cache = [
        'underscore'   => [],
        'methodName'   => [],
        'methodName'   => [],
        'variableName' => [],
        'className'    => [],
    ];

    public static function underscore(string $str): string
    {
        if ( ! isset(static::$cache['underscore'][$str])) {
            $str = strtolower($str);
            $str = preg_replace("/[^a-z0-9]+/", ' ', $str);

            static::$cache['underscore'][$str] = str_replace(' ', '_', $str);
        }

        return static::$cache['underscore'][$str];
    }

    public static function methodName(string $str, string $verb): string
    {
        $key = $verb . $str;
        if ( ! isset(static::$cache['methodName'][$key])) {
            static::$cache['methodName'][$key] = strtolower($verb) . static::className($str);
        }

        return static::$cache['methodName'][$key];
    }

    public static function variableName(string $str): string
    {
        if ( ! isset(static::$cache['variableName'][$str])) {
            $class = static::className($str);

            static::$cache['variableName'][$str] = strtolower(substr($class, 0, 1)) . substr($class, 1);
        }

        return static::$cache['variableName'][$str];
    }

    public static function className(string $str): string
    {
        if ( ! isset(static::$cache['className'][$str])) {
            $str = strtolower($str);
            $str = preg_replace("/[^a-z0-9]+/", ' ', $str);
            $str = ucwords($str);

            static::$cache['className'][$str] = str_replace(' ', '', $str);
        }

        return static::$cache['className'][$str];
    }
}
