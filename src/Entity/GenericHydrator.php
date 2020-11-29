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

class GenericHydrator implements HydratorInterface
{
    /**
     * @var CastingManager
     */
    protected $castingManager;

    /**
     * @var Mapper
     */
    protected $mapper;

    public function __construct(CastingManager $castingManager)
    {
        $this->castingManager = $castingManager;
    }

    public function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
    }

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

    /**
     * @param EntityInterface $entity
     *
     * @return array
     */
    public function extract(EntityInterface $entity)
    {
        $data = Arr::renameKeys(
            $entity->toArray(),
            array_flip($this->getMapperConfig()->getColumnAttributeMap())
        );
        if ($this->castingManager) {
            $data = $this->castingManager
                ->castArrayForDb($data, $this->getMapperConfig()->getCasts());
        }

        return Arr::only($data, $this->getMapperConfig()->getColumns());
    }

    /**
     * @param $entity
     * @param $attribute
     *
     * @return mixed
     */
    public function get(EntityInterface $entity, $attribute)
    {
        return $entity->{$attribute};
    }

    /**
     * @param $entity
     * @param $attribute
     * @param $value
     *
     * @return mixed
     */
    public function set(EntityInterface $entity, $attribute, $value)
    {
        if ($value instanceof LazyLoader) {
            return $entity->setLazy($attribute, $value);
        }

        return $entity->{$attribute} = $value;
    }

    /**
     * @param $entity
     *
     * @return mixed
     */
    public function getPk($entity)
    {
        return $this->get($entity, $this->getMapperConfig()->getPrimaryKey());
    }

    /**
     * Set primary key on an entity
     *
     * @param $entity
     * @param $value
     *
     * @return mixed
     */
    public function setPk($entity, $value)
    {
        return $this->set($entity, $this->getMapperConfig()->getPrimaryKey(), $value);
    }

    protected function compileRelations(array $attributes)
    {
        foreach ($this->mapper->getRelations() as $name) {
            $relation = $this->mapper->getRelation($name);
            if ($relation instanceof ToOneInterface &&
                isset($attributes[$name]) &&
                ! is_object($attributes[$name])) {
                $attributes[$name] = $relation->getForeignMapper()->newEntity($attributes[$name]);
            } elseif ($relation instanceof ToManyInterface &&
                      ! $relation instanceof ToOneInterface
                      && ( ! isset($attributes[$name]) || is_array($attributes[$name]))) {
                /**
                 * we also need to check against ToOneInterface because OneToOne relation extends
                 * OneToMany which implements ToOneInterface
                 * @todo remove this quirk
                 */
                $attributes[$name] = $relation->getForeignMapper()->newCollection($attributes[$name] ?? []);
            }
        }

        return $attributes;
    }

    protected function getMapperConfig(): MapperConfig
    {
        return $this->mapper->getConfig();
    }
}
