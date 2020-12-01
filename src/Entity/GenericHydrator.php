<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

use Sirius\Orm\Contract\EntityInterface;
use Sirius\Orm\Contract\LazyLoader;

class GenericHydrator extends AbstractHydrator
{

    /**
     * @param array $attributes
     *
     * @return mixed|GenericEntity
     */
    public function hydrate(array $attributes = [])
    {
        $attributes = $this->hydrateToArray($attributes);

        $class = $this->getMapperConfig()->getEntityClass() ?? GenericEntity::class;

        return new $class($attributes);
    }

    public function get(EntityInterface $entity, string $attribute)
    {
        return $entity->{$attribute};
    }

    public function set(EntityInterface $entity, string $attribute, $value)
    {
        if ($value instanceof LazyLoader) {
            return $entity->setLazy($attribute, $value);
        }

        return $entity->{$attribute} = $value;
    }
}
