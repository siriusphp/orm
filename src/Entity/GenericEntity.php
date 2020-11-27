<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

use Sirius\Orm\Contract\EntityInterface;
use Sirius\Orm\Contract\LazyLoader;
use Sirius\Orm\Helpers\Str;

class GenericEntity implements EntityInterface
{
    use BaseEntityTrait;

    protected $attributes = [];


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

    protected function castAttribute($name, $value)
    {
        $method = Str::methodName($name . ' attribute', 'cast');
        if (method_exists($this, $method)) {
            return $this->$method($value);
        }

        return $value;
    }

    protected function set(string $attribute, $value = null)
    {
        if ($value instanceof LazyLoader) {
            $this->lazyLoaders[$attribute] = $value;
            return;
        }

        $value = $this->castAttribute($attribute, $value);
        if ( ! isset($this->attributes[$attribute]) || $value != $this->attributes[$attribute]) {
            $this->markChanged($attribute);
            $this->state               = StateEnum::CHANGED;
        }
        $this->attributes[$attribute] = $value;
    }

    protected function get(string $attribute)
    {
        $this->maybeLazyLoad($attribute);

        return $this->attributes[$attribute] ?? null;
    }
}
