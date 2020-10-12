<?php
declare(strict_types=1);

namespace Sirius\Orm\Collection;

use Doctrine\Common\Collections\ArrayCollection;

class Collection extends ArrayCollection
{
    protected $changes = [
        'removed' => [],
        'added'   => []
    ];
    /**
     * @var callable
     */
    protected $castingFunction;

    public function __construct(array $elements = [], callable $castingFunction = null)
    {
        parent::__construct($elements);
        $this->changes['removed'] = new ArrayCollection();
        $this->changes['added']   = new ArrayCollection();
        $this->castingFunction    = $castingFunction;
    }

    protected function castElement($data)
    {
        $castFunction = $this->castingFunction;

        return $castFunction ? call_user_func($castFunction, $data) : $data;
    }

    public function add($element)
    {
        $element = $this->castElement($element);
        $this->change('added', $element);

        return parent::add($element);
    }

    public function set($key, $value)
    {
        $value = $this->castElement($value);
        parent::set($key, $value);
    }

    public function remove($key)
    {
        $removed = parent::remove($key);
        if ($removed) {
            $this->change('removed', $removed);
        }

        return $removed;
    }

    public function removeElement($element)
    {
        $element = $this->castElement($element);
        $removed = parent::removeElement($element);
        if ($removed) {
            $this->change('removed', $element);
        }

        return $removed;
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

    protected function change($type, $element)
    {
        foreach (array_keys($this->changes) as $t) {
            /** @var ArrayCollection $changeCollection */
            $changeCollection = $this->changes[$t];
            if ($t == $type) {
                if ( ! $changeCollection->contains($element)) {
                    $changeCollection->add($element);
                }
            } else {
                $this->removeElement($element);
            }
        }
    }
}
