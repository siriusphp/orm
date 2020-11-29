<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

use Sirius\Orm\CastingManager;
use Sirius\Orm\Contract\EntityInterface;
use Sirius\Orm\Contract\HydratorInterface;
use Sirius\Orm\Contract\LazyLoader;
use Sirius\Orm\Contract\Relation\ToManyInterface;
use Sirius\Orm\Contract\Relation\ToOneInterface;
use Sirius\Orm\Helpers\Arr;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;

class GenericHydrator extends AbstractHydrator
{

    /**
     * @param array $attributes
     *
     * @return mixed|GenericEntity
     */
    public function hydrate(array $attributes = [])
    {
        $attributes = Arr::renameKeys($attributes, $this->getMapperConfig()->getColumnAttributeMap());
        if ($this->castingManager) {
            $attributes = $this->castingManager
                ->castArray($attributes, $this->getMapperConfig()->getCasts());
        }

        $attributes = $this->compileRelations($attributes);

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
