<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Contract\EntityInterface;
use Sirius\Orm\Contract\Relation\ToManyInterface;
use Sirius\Orm\Contract\Relation\ToOneInterface;
use Sirius\Orm\Helpers\Arr;
use Sirius\Orm\Mapper;

class Patcher
{
    /**
     * @var Mapper
     */
    protected $mapper;

    public function __construct(Mapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function __invoke(EntityInterface $entity, array $attributes)
    {
        $names      = $this->mapper->getConfig()->getAttributeNames();
        $properties = $this->mapper->getHydrator()->hydrateToArray(Arr::only($attributes, $names));
        $relations  = array_keys($this->mapper->getRelations());
        foreach (array_keys($attributes) as $attr) {
            // we have an attribute
            if (in_array($attr, $names) && isset($properties[$attr])) {
                $this->mapper->getHydrator()->set($entity, $attr, $properties[$attr]);
                continue;
            }

            // we have a relation
            if (in_array($attr, $relations)) {
                $this->setRelated($entity, $attr, $attributes[$attr]);
            }
        }

        return $entity;
    }

    protected function setRelated(EntityInterface $entity, string $relationName, $attributes)
    {
        $relation = $this->mapper->getRelation($relationName);
        if ($relation instanceof ToOneInterface) {
            $this->patchSingle($entity, $relationName, $attributes);
        } elseif ($relation instanceof ToManyInterface) {
            $this->patchCollection($entity, $relationName, $attributes);
        }
    }

    private function patchSingle(EntityInterface $entity, string $relationName, $attributes)
    {
        if (!is_array($attributes)) {
            $this->mapper->getHydrator()->set($entity, $relationName, $attributes);
            return;
        }
        $foreignMapper = $this->mapper->getRelation($relationName)
                                      ->getForeignMapper();
        $currentValue  = $this->mapper->getHydrator()->get($entity, $relationName);
        $this->patchOrCreate($foreignMapper, $currentValue, $attributes);
        $this->mapper->getHydrator()->set($entity, $relationName, $currentValue);
    }

    private function patchCollection(EntityInterface $entity, string $relationName, $attributes)
    {
        $foreignMapper = $this->mapper->getRelation($relationName)
                                      ->getForeignMapper();
        /** @var Collection|null $collection */
        $collection  = $this->mapper->getHydrator()->get($entity, $relationName);
        if (!$collection || $collection->isEmpty()) {
            $this->mapper->getHydrator()->set($entity, $relationName, $foreignMapper->newCollection($attributes));
            return;
        }

        $newCollection = $foreignMapper->newCollection($attributes);

        // first remove the elements from the current collection
        // that are not present in the new collection
        foreach ($collection as $item) {
            if (!$newCollection->contains($item)) {
                $collection->removeElement($item);
                continue;
            }
        }

        // add or update elements in the current collection
        // that exist in the new collection
        foreach ($newCollection as $idx => $item) {
            $pk = $foreignMapper->getHydrator()->getPk($item);
            if (!$pk || !$collection->contains($item)) {
                $collection->add($item);
                continue;
            }

            $crtItem = $collection->findByPk($pk);
            if ($crtItem) {
                $foreignMapper->patch($crtItem, $attributes[$idx]);
            } else {
                // fallback for when the item is not found
                $collection->add($item);
            }
        }
    }

    private function patchOrCreate(Mapper $mapper, EntityInterface $entity = null, array $attributes = [])
    {
        if ($entity) {
            return $mapper->patch($entity, $attributes);
        }

        return $mapper->newEntity($attributes);
    }
}
