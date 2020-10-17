<?php
declare(strict_types=1);

namespace Sirius\Orm\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Sirius\Orm\Contract\EntityInterface;
use Sirius\Orm\Contract\HydratorInterface;

class Collection extends ArrayCollection
{
    protected $changes = [
        'removed' => [],
        'added'   => []
    ];

    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    protected $primaryKey;

    public function __construct(array $elements = [], HydratorInterface $hydrator, $primaryKey)
    {
        parent::__construct($elements);
        $this->hydrator           = $hydrator;
        $this->primaryKey         = $primaryKey;
        $this->changes['removed'] = new ArrayCollection();
        $this->changes['added']   = new ArrayCollection();
    }

    protected function ensureHydratedElement($element)
    {
        if ( ! $element instanceof EntityInterface) {
            return $this->hydrator->hydrate((array)$element);
        }

        return $element;
    }

    protected function getElementPK($element)
    {
        return $this->hydrator->getPk($element);
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
        if ( ! $this->contains($element)) {
            $this->change('added', $element);

            return parent::add($element);
        }
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
        $element = $this->ensureHydratedElement($element);
        if ( ! $this->contains($element)) {
            return true;
        }
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
