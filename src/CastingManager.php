<?php
declare(strict_types=1);

namespace Sirius\Orm;

class CastingManager
{
    protected $casts = [];

    /**
     * @var CastingManager
     */
    protected static $instance;

    public static function getInstance()
    {
        if ( ! static::$instance) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    public function register(string $name, callable $func)
    {
        $this->casts[$name] = $func;
    }

    public function cast($type, $value, ...$args)
    {
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

    public function castArray($arr, $rules)
    {
        $result = [];

        foreach ($arr as $col => $val) {
            if (isset($rules[$col])) {
                $result[$col] = $this->cast($rules[$col], $val);
            } else {
                $result[$col] = $val;
            }
        }

        return $result;
    }

    public function castArrayForDb($arr, $rules)
    {
        $result = [];

        foreach ($arr as $col => $val) {
            if (isset($rules[$col])) {
                $result[$col] = $this->cast($rules[$col] . '_for_db', $val);
            } else {
                $result[$col] = $val;
            }
        }

        return $result;
    }

    public function bool($value)
    {
        if ($value === '0' || $value === '' || floatval($value) === 0) {
            return false;
        }

        return ! ! $value;
    }

    // phpcs:ignore
    public function bool_for_db($value)
    {
        return $value ? 1 : 0;
    }

    public function int($value)
    {
        return $value === null ? null : (int)$value;
    }

    public function float($value)
    {
        return $value === null ? null : (float)$value;
    }

    public function decimal($value, $digits)
    {
        return round((float)$value, (int)$digits);
    }

    public function array($value) {
        if ($value === null) {
            return null;
        }
        if (is_array($value)) {
            return $value;
        }

        return json_decode((string) $value, true);
    }

    public function json($value)
    {
        if ( ! $value) {
            return new \ArrayObject();
        }
        if (is_array($value)) {
            return new \ArrayObject($value);
        }
        if (is_string($value)) {
            return new \ArrayObject(json_decode($value, true));
        }
        if ($value instanceof \ArrayObject) {
            return $value;
        }
        throw new \InvalidArgumentException('Value has to be a string, an array or an ArrayObject');
    }

    // phpcs:ignore
    public function json_for_db($value)
    {
        if ( ! $value) {
            return null;
        }
        if (is_array($value)) {
            return json_encode($value);
        }
        if ($value instanceof \ArrayObject) {
            return json_encode($value->getArrayCopy());
        }
        if (is_object($value) && method_exists($value, 'toArray')) {
            return json_encode($value->toArray());
        }
        if (is_object($value) && method_exists($value, 'getArrayCopy')) {
            return json_encode($value->getArrayCopy());
        }

        return $value;
    }
}
