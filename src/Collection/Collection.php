<?php
declare(strict_types=1);

namespace Sirius\Orm\Collection;

use Doctrine\Common\Collections\ArrayCollection;

class Collection extends ArrayCollection
{
    protected $changes = [
        'removed' => [],
        'deleted' => [],
        'added'   => []
    ];

    public function __construct(array $elements = [])
    {
        parent::__construct($elements);
        $this->changes['removed'] = new ArrayCollection();
        $this->changes['deleted'] = new ArrayCollection();
        $this->changes['added']   = new ArrayCollection();
    }

    public function add($element)
    {
        $this->change('added', $element);

        return parent::add($element);
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
        $removed = parent::removeElement($element);
        if ($removed) {
            $this->change('removed', $element);
        }

        return $removed;
    }

    public function delete($element)
    {
        $removed = parent::removeElement($element);
        if ($removed) {
            $this->change('deleted', $element);
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

    public function getArrayCopy()
    {
        return $this->getValues();
    }

    protected function change($type, $element)
    {
        foreach (array_keys($this->changes) as $t) {
            /** @var ArrayCollection $changeCollection */
            $changeCollection = $this->changes[$t];
            if ($t == $type) {
                if (! $changeCollection->contains($element)) {
                    $changeCollection->add($element);
                }
            } else {
                $this->removeElement($element);
            }
        }
    }
}
