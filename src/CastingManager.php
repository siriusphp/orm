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

        if (method_exists($this, $type)) {
            return $this->$type($value, ...$args);
        }

        if (isset($this->casts[$type])) {
            $func = $this->casts[$type];

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
        return $value === null ? null : int($value);
    }

    public function float($value)
    {
        return $value === null ? null : float($value);
    }
}
