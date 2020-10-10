<?php
declare(strict_types=1);

namespace Sirius\Orm\Helpers;

use Symfony\Component\Inflector\Inflector as InflectorAlias;

class Inflector
{
    public static function singularize(string $plural)
    {
        $result = InflectorAlias::singularize($plural);

        return is_array($result) ? end($result) : $result;
    }

    public static function pluralize(string $singular)
    {
        $result = InflectorAlias::pluralize($singular);

        return is_array($result) ? end($result) : $result;
    }
}
