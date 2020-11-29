<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

use Sirius\Orm\Contract\EntityInterface;
use Sirius\Orm\Contract\LazyLoader;
use Sirius\Orm\Helpers\Arr;
use Sirius\Orm\Helpers\Str;

class ClassMethodsHydrator extends AbstractHydrator
{
    /**
     * @param array $attributes
     *
     * @return mixed|ClassMethodsEntity
     */
    public function hydrate(array $attributes = [])
    {
        $attributes = Arr::renameKeys($attributes, $this->getMapperConfig()->getColumnAttributeMap());
        if ($this->castingManager) {
            $attributes = $this->castingManager
                ->castArray($attributes, $this->getMapperConfig()->getCasts());
        }

        $attributes = $this->compileRelations($attributes);

        $class = $this->getMapperConfig()->getEntityClass() ?? ClassMethodsEntity::class;

        return new $class($attributes);
    }

    public function get(EntityInterface $entity, $attribute)
    {
        $method = Str::methodName($attribute, 'get');

        return $entity->{$method}();
    }

    public function set(EntityInterface $entity, $attribute, $value)
    {
        if ($value instanceof LazyLoader) {
            return $entity->setLazy($attribute, $value);
        }

        $method = Str::methodName($attribute, 'set');

        return $entity->{$method}($value);
    }
}
