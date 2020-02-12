<?php
declare(strict_types=1);

namespace Sirius\Orm\Helpers;

class Arr
{
    public static function getChildren(array $arr, string $path, string $separator = '.')
    {
        $children  = [];
        $prefix    = $path . $separator;
        $prefixLen = strlen($prefix);
        foreach ($arr as $key => $value) {
            if (substr($key, 0, $prefixLen) != $prefix) {
                continue;
            }
            $children[substr($key, $prefixLen)] = $value;
        }

        return $children;
    }

    public static function ensureParents(array $arr, string $separator = '.'): array
    {
        foreach ($arr as $key => $value) {
            if (strpos($key, $separator)) {
                $parents = static::getParents($key, $separator);
                foreach ($parents as $parent) {
                    if (! isset($arr[$parent])) {
                        $arr[$parent] = null;
                    }
                }
            }
        }

        return $arr;
    }

    protected static function getParents(string $path, string $separator): array
    {
        $parts   = explode($separator, substr($path, 0, strrpos($path, $separator)));
        $current = '';
        $parents = [];
        foreach ($parts as $part) {
            $current   = $current . ($current ? $separator : '') . $part;
            $parents[] = $current;
        }

        return $parents;
    }

    public static function only(array $arr, array $keys)
    {
        return array_intersect_key($arr, array_flip((array)$keys));
    }

    public static function except(array $arr, $keys)
    {
        foreach ((array)$keys as $key) {
            unset($arr[$key]);
        }

        return $arr;
    }
}
