<?php
declare(strict_types=1);

namespace Sirius\Orm\Collection;

use Closure;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Exception;
use Sirius\Orm\Contract\EntityInterface;
use Sirius\Orm\Contract\HydratorInterface;
use Sirius\Orm\Entity\StateEnum;
use Traversable;

class Collection implements \Doctrine\Common\Collections\Collection, Selectable
{
    protected $changes = [
        'removed' => [],
        'added'   => []
    ];

    /**
     * @var HydratorInterface|null
     */
    protected $hydrator;

    /**
     * @var ArrayCollection
     */
    protected $collection;

    public function __construct(array $elements = [], HydratorInterface $hydrator = null)
    {
        $this->hydrator           = $hydrator;
        $this->collection         = new ArrayCollection($elements);
        $this->changes['removed'] = new ArrayCollection();
        $this->changes['added']   = new ArrayCollection();
    }

    protected function ensureHydratedElement($element)
    {
        if (! $this->hydrator) {
            return $element;
        }

        if ($element instanceof EntityInterface) {
            return $element;
        }

        if (is_array($element)) {
            return $this->hydrator->hydrate((array)$element);
        }

        throw new \InvalidArgumentException('You can only add arrays or entities to collections');
    }

    protected function getElementPK($element)
    {
        return $this->hydrator ? $this->hydrator->getPk($element) : null;
    }

    public function contains($element)
    {
        $pk = $this->getElementPK($this->ensureHydratedElement($element));
        if ($pk === null || $pk === []) {
            return false;
        }
        foreach ($this as $element) {
            if ($pk == $this->getElementPK($element)) {
                return true;
            }
        }

        return false;
    }

    public function add($element)
    {
        $element = $this->ensureHydratedElement($element);
        if (! $this->contains($element)) {
            $this->change('added', $element);

            return $this->collection->add($element);
        }

        return true;
    }

    public function remove($key)
    {
        $removed = $this->collection->remove($key);
        if ($removed) {
            $this->change('removed', $removed);
        }

        $this->collection = new ArrayCollection($this->collection->getValues());

        return $removed;
    }

    public function removeElement($element)
    {
        $element = $this->ensureHydratedElement($element);
        if (! $this->contains($element)) {
            return true;
        }
        $removed = $this->collection->removeElement($this->findByPk($this->hydrator->getPk($element)));
        if ($removed) {
            $this->change('removed', $element);
        }
        $this->collection = new ArrayCollection($this->collection->getValues());

        return true;
    }

    public function findByPk($pk)
    {
        foreach ($this as $element) {
            if ($pk == $this->hydrator->getPk($element)) {
                return $element;
            }
        }

        return null;
    }

    public function pluck($names)
    {
        return $this->map(function ($item) use ($names) {
            if (! is_array($names)) {
                return $this->hydrator->get($item, $names);
            }

            $result = [];
            foreach ($names as $name) {
                $result[$name] = $this->hydrator->get($item, $name);
            }

            return $result;
        })->getValues();
    }

    public function reduce(\Closure $callback, $accumulator)
    {
        return array_reduce($this->getValues(), $callback, $accumulator);
    }

    public function getChanges(): array
    {
        $changes = [];
        foreach (array_keys($this->changes) as $t) {
            /** @var ArrayCollection $changeCollection */
            $changeCollection = $this->changes[$t];
            $changes[$t]      = $changeCollection->getValues();
        }

        return $changes;
    }

    public function toArray()
    {
        $result = [];
        foreach ($this as $element) {
            if (is_object($element) && method_exists($element, 'toArray')) {
                $result[] = $element->toArray();
            } else {
                $result[] = $element;
            }
        }

        return $result;
    }

    public function setState($state)
    {
        if ($state == StateEnum::SYNCHRONIZED) {
            $this->changes['removed'] = new ArrayCollection();
            $this->changes['added']   = new ArrayCollection();
        }
    }

    protected function change($type, $element)
    {
        $changeCollection = $this->changes[$type];
        $changeCollection->add($element);
    }

    public function clear()
    {
        $this->collection->clear();
    }

    public function isEmpty()
    {
        return $this->collection->isEmpty();
    }

    public function containsKey($key)
    {
        return $this->collection->containsKey($key);
    }

    public function get($key)
    {
        return $this->collection->get($key);
    }

    public function getKeys()
    {
        return $this->collection->getKeys();
    }

    public function getValues()
    {
        return $this->collection->getValues();
    }

    public function set($key, $value)
    {
        $this->collection->set($key, $value);
    }

    public function first()
    {
        return $this->collection->first();
    }

    public function last()
    {
        return $this->collection->last();
    }

    public function key()
    {
        return $this->collection->key();
    }

    public function current()
    {
        return $this->collection->current();
    }

    public function next()
    {
        return $this->collection->next();
    }

    public function exists(Closure $p)
    {
        return $this->collection->exists($p);
    }

    public function filter(Closure $p)
    {
        return $this->collection->filter($p);
    }

    public function forAll(Closure $p)
    {
        return $this->collection->forAll($p);
    }

    public function map(Closure $func)
    {
        return $this->collection->map($func);
    }

    public function partition(Closure $p)
    {
        return $this->collection->partition($p);
    }

    public function indexOf($element)
    {
        return $this->collection->indexOf($element);
    }

    public function slice($offset, $length = null)
    {
        return $this->collection->slice($offset, $length);
    }

    public function getIterator()
    {
        return $this->collection->getIterator();
    }

    public function offsetExists($offset)
    {
        return $this->collection->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->collection->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->collection->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->collection->offsetUnset($offset);
    }

    public function count()
    {
        return $this->collection->count();
    }

    public function matching(Criteria $criteria)
    {
        return $this->collection->matching($criteria);
    }
}
