<?php
declare(strict_types=1);

namespace Sirius\Orm;

class CastingManager
{
    protected $casts = [];

    public function register($name, callable $func)
    {
        if (! $name) {
            return; // ignore
        }
        $this->casts[$name] = $func;
    }

    public function cast($type, $value, ...$args)
    {
        if ($value === null) {
            return null;
        }

        if (strpos($type, ':')) {
            list($cast, $args) = explode(':', $type);
            $args = explode(',', $args);
        } else {
            $cast = $type;
        }

        if (method_exists($this, $cast)) {
            return $this->$cast($value, ...$args);
        }

        if (isset($this->casts[$cast])) {
            $func = $this->casts[$cast];

            return $func($value, ...$args);
        }

        return $value;
    }

    public function bool($value)
    {
        return ! ! $value;
    }

    public function int($value)
    {
        return $value === null ? null : (int)$value;
    }

    public function float($value)
    {
        return $value === null ? null : float($value);
    }

    public function decimal($value, $digits)
    {
        return round((float)$value, (int)$digits);
    }
}
