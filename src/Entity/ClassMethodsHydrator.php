<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

use Sirius\Orm\CastingManager;
use Sirius\Orm\Contract\CastingManagerAwareInterface;
use Sirius\Orm\Contract\EntityInterface;
use Sirius\Orm\Contract\HydratorInterface;
use Sirius\Orm\Contract\LazyLoader;
use Sirius\Orm\Helpers\Arr;
use Sirius\Orm\MapperConfig;

class ClassMethodsHydrator implements HydratorInterface
{
    /**
     * @var CastingManager
     */
    protected $castingManager;

    /**
     * @var MapperConfig
     */
    protected $mapperConfig;

    public function __construct(CastingManager $castingManager)
    {
        $this->castingManager = $castingManager;
    }

    /**
     * @param MapperConfig $mapperConfig
     *
     * @return GenericHydrator
     */
    public function setMapperConfig(MapperConfig $mapperConfig): GenericHydrator
    {
        $this->mapperConfig = $mapperConfig;

        return $this;
    }

    /**
     * @param array $attributes
     *
     * @return mixed|ClassMethodsEntity
     */
    public function hydrate(array $attributes = [])
    {
        $attributes = Arr::renameKeys($attributes, $this->mapperConfig->getColumnAttributeMap());
        if ($this->castingManager) {
            $attributes = $this->castingManager
                ->castArray($attributes, $this->mapperConfig->getCasts());
        }

        $class = $this->mapperConfig->getEntityClass() ?? GenericEntity::class;

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
            array_flip($this->mapperConfig->getColumnAttributeMap())
        );
        if ($this->castingManager) {
            $data = $this->castingManager
                ->castArrayForDb($data, $this->mapperConfig->getCasts());
        }

        return Arr::only($data, $this->mapperConfig->getColumns());
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
        return $entity->{$attribute} = $value;
    }

    public function setLazy(EntityInterface $entity, $attribute, LazyLoader $lazyLoader)
    {
        $entity->setLazy($attribute, $lazyLoader);
    }

    /**
     * @param $entity
     *
     * @return mixed
     */
    public function getPk($entity)
    {
        return $this->get($entity, $this->mapperConfig->getPrimaryKey());
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
        return $this->set($entity, $this->mapperConfig->getPrimaryKey(), $value);
    }
}
