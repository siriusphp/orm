<?php
declare(strict_types=1);

namespace Sirius\Orm\Helpers;

class Inflector
{
    public static function singularize(string $plural)
    {
        $result = \Symfony\Component\Inflector\Inflector::singularize($plural);

        return is_array($result) ? end($result) : $result;
    }

    public static function pluralize(string $singular)
    {
        $result = \Symfony\Component\Inflector\Inflector::pluralize($singular);

        return is_array($result) ? end($result) : $result;
    }
}
