<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

use Sirius\Orm\CastingManager;
use Sirius\Orm\Helpers\Str;

class GenericEntity implements EntityInterface
{
    protected $state = StateEnum::CHANGED;

    protected $primaryKey = 'id';

    protected $attributes = [];

    protected $lazyLoaders = [];

    protected $changed = [];

    protected $casts = [];

    public function __construct(array $attributes, CastingManager $castingManager = null)
    {
        $this->castingManager = $castingManager;
        foreach ($attributes as $attr => $value) {
            $this->set($attr, $value);
        }
    }

    protected function castAttribute($name, $value)
    {
        $method = Str::methodName($name . ' attribute', 'cast');
        if (method_exists($this, $method)) {
            return $this->$method($value);
        }

        if (!$this->castingManager) {
            return $value;
        }

        /**
         * @todo implement additional attributes
         */
        $type = $this->casts[$name] ?? $name;

        return $this->castingManager->cast($type, $value);
    }

    public function getPk()
    {
        /**
         * @todo implement a way to retrieve the proper PK columns
         */
        return $this->get($this->primaryKey);
    }

    public function setPk($val)
    {
        /**
         * @todo implement a way to retrieve the proper PK columns
         */
        $this->set($this->primaryKey, $val);
    }

    public function set($attribute, $value = null)
    {
        $this->preventChangesIfDeleted();

        if ($value instanceof LazyValueLoader) {
            $this->lazyLoaders[$attribute] = $value;

            return $this;
        }

        $value = $this->castAttribute($attribute, $value);
        if (! isset($this->attributes[$attribute]) || $value != $this->attributes[$attribute]) {
            $this->changed[$attribute] = true;
            $this->state               = StateEnum::CHANGED;
        }
        $this->attributes[$attribute] = $value;

        return $this;
    }

    public function get($attribute)
    {
        if (! $attribute) {
            return null;
        }

        $this->maybeLazyLoad($attribute);

        return $this->attributes[$attribute] ?? null;
    }

    public function getPersistanceState()
    {
        if (! empty($this->changed)) {
        }

        return $this->state;
    }

    public function setPersistanceState($state)
    {
        if ($state == StateEnum::SYNCHRONIZED) {
            $this->changed = [];
        }
        $this->state = $state;
    }

    public function getArrayCopy()
    {
        return $this->attributes;
    }

    public function getChanges()
    {
        $changes = $this->changed;
        foreach ($this->attributes as $name => $value) {
            if (is_object($value) && method_exists($value, 'getChanges')) {
                if (! empty($value->getChanges())) {
                    $changes[$name] = true;
                }
            }
        }

        return $changes;
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
            /** @var LazyValueLoader $lazyLoader */
            $lazyLoader = $this->lazyLoaders[$attribute];
            $lazyLoader->load();
            unset($this->changed[$attribute]);
            unset($this->lazyLoaders[$attribute]);
        }
    }
}
