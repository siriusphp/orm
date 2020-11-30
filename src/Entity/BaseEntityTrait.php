<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

use Sirius\Orm\Contract\LazyLoader;

trait BaseEntityTrait
{
    /**
     * @var string
     */
    protected $state;

    /**
     * @var array
     */
    protected $changed = [];

    protected $lazyLoaders = [];

    /**
     * Marks an attribute as being changed
     * @param $attribute
     */
    protected function markChanged($attribute)
    {
        $this->changed[$attribute] = true;
    }


    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Sets the persistance-related state (syncronized, deleted or changed)
     * Saving or changing the entity behave differently depending on its state
     *
     * @param $state
     */
    public function setState($state)
    {
        // a syncronized entity assumes the attributes
        // and fields in the database are the same
        if ($state == StateEnum::SYNCHRONIZED) {
            $this->changed = [];
        }
        $this->state = $state;
    }

    public function setLazy(string $name, LazyLoader $lazyLoader)
    {
        if ($lazyLoader instanceof LazyValue) {
            $this->attributes[$name] = $lazyLoader->getForEntity($this);
            return;
        }
        $this->lazyLoaders[$name] = $lazyLoader;
    }

    /**
     * Returns an array representation of the entity
     *
     * @todo implement option to force loading of specific relations (for when the relations are lazy loaded)
     *
     * @return mixed
     */
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

    /**
     * Returns the list of entity changes
     *
     * @return array
     */
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

    protected function maybeLazyLoad($attribute): void
    {
        if (isset($this->lazyLoaders[$attribute])) {
            // preserve state
            $state = $this->state;
            /** @var LazyLoader $lazyLoader */
            $lazyLoader = $this->lazyLoaders[$attribute];
            if ($lazyLoader instanceof LazyValue) {
                $this->attributes[$attribute] = $lazyLoader->getForEntity($this);
            } else {
                $lazyLoader->getForEntity($this);
            }
            unset($this->lazyLoaders[$attribute]);
            unset($this->changed[$attribute]);
            $this->state = $state;
        }
    }
}
