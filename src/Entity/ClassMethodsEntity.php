<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

use Sirius\Orm\Contract\EntityInterface;
use Sirius\Orm\Contract\LazyLoader;
use Sirius\Orm\Helpers\Str;

class ClassMethodsEntity implements EntityInterface
{
    use BaseEntityTrait;

    protected $attributes = [];

    protected $lazyLoaders = [];

    public function __construct(array $attributes, string $state = null)
    {
        foreach ($attributes as $attr => $value) {
            $this->set($attr, $value);
        }
        $this->setState($state);
    }

    public function __call($method, $args)
    {
        if (substr($method, 0, 3) === 'get') {
            return $this->get(Str::underscore(substr($method, 3)));
        }

        if (substr($method, 0, 3) === 'set') {
            return $this->set(Str::underscore(substr($method, 3)), $args[0]);
        }

        throw new \BadMethodCallException("Unknown {$method}() called on " . get_class($this));
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

        if ($value instanceof LazyLoader) {
            $this->lazyLoaders[$attribute] = $value;

            return $this;
        }

        $value = $this->castAttribute($attribute, $value);
        if ( ! isset($this->attributes[$attribute]) || $value != $this->attributes[$attribute]) {
            $this->markChanged($attribute);
            $this->state = StateEnum::CHANGED;
        }
        $this->attributes[$attribute] = $value;

        return $this;
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
