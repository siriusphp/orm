<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

use Sirius\Orm\Contract\EntityInterface;
use Sirius\Orm\Contract\LazyLoader;
use Sirius\Orm\Helpers\Str;

class GenericEntity implements EntityInterface
{
    protected $state = StateEnum::SYNCHRONIZED;

    protected $attributes = [];

    protected $lazyLoaders = [];

    protected $changed = [];

    public function __construct(array $attributes = [], string $state = null)
    {
        foreach ($attributes as $attr => $value) {
            $this->set($attr, $value);
        }
        $this->setState($state);
    }

    public function __get($name)
    {
        $method = Str::methodName($name . ' attribute', 'get');
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return $this->get($name);
    }

    public function __set($name, $value)
    {
        $method = Str::methodName($name . ' attribute', 'set');
        if (method_exists($this, $method)) {
            return $this->$method($value);
        }

        return $this->set($name, $value);
    }

    public function __isset($name)
    {
        return isset($this->attributes[$name]);
    }

    public function __unset($name)
    {
        unset($this->attributes[$name]);
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        if ($state == StateEnum::SYNCHRONIZED) {
            $this->changed = [];
        }
        $this->state = $state;
    }

    public function toArray()
    {
        $copy = $this->attributes;
        foreach ($copy as $k => $v) {
            if (is_object($v) && method_exists($v, 'toArray')) {
                $copy[$k] = $v->toArray();
            }
        }

        return $copy;
    }

    public function getChanges()
    {
        $changes = $this->changed;
        foreach ($this->attributes as $name => $value) {
            if (is_object($value) && method_exists($value, 'getChanges')) {
                if ( ! empty($value->getChanges())) {
                    $changes[$name] = true;
                }
            }
        }

        return $changes;
    }

    protected function castAttribute($name, $value)
    {
        $method = Str::methodName($name . ' attribute', 'cast');
        if (method_exists($this, $method)) {
            return $this->$method($value);
        }

        return $value;
    }

    protected function set($attribute, $value = null)
    {
        $this->preventChangesIfDeleted();

        $value = $this->castAttribute($attribute, $value);
        if ( ! isset($this->attributes[$attribute]) || $value != $this->attributes[$attribute]) {
            $this->changed[$attribute] = true;
            $this->state               = StateEnum::CHANGED;
        }
        $this->attributes[$attribute] = $value;
    }

    protected function get($attribute)
    {
        if ( ! $attribute) {
            return null;
        }

        $this->maybeLazyLoad($attribute);

        return $this->attributes[$attribute] ?? null;
    }

    public function setLazy($attribute, LazyLoader $lazyLoader)
    {
        $this->lazyLoaders[$attribute] = $lazyLoader;

        return $this;
    }

    protected function preventChangesIfDeleted()
    {
        if ($this->state == StateEnum::DELETED) {
            throw new \BadMethodCallException('Entity was deleted, no further changes are allowed');
        }
    }

    /**
     * @param $attribute
     */
    protected function maybeLazyLoad($attribute): void
    {
        if (isset($this->lazyLoaders[$attribute])) {
            // preserve state
            $state = $this->state;
            /** @var LazyLoader $lazyLoader */
            $lazyLoader = $this->lazyLoaders[$attribute];
            $lazyLoader->getForEntity($this);
            unset($this->changed[$attribute]);
            unset($this->lazyLoaders[$attribute]);
            $this->state = $state;
        }
    }
}
