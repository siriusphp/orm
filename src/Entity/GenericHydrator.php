<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

use Sirius\Orm\CastingManager;
use Sirius\Orm\Contract\CastingManagerAwareInterface;
use Sirius\Orm\Contract\EntityInterface;
use Sirius\Orm\Contract\HydratorInterface;
use Sirius\Orm\Helpers\Arr;
use Sirius\Orm\MapperConfig;

class GenericHydrator implements HydratorInterface, CastingManagerAwareInterface
{
    /**
     * @var CastingManager
     */
    protected $castingManager;

    /**
     * @var MapperConfig
     */
    protected $mapperConfig;

    /**
     * @param ?CastingManager $castingManager
     *
     * @return GenericHydrator
     */
    public function setCastingManager(CastingManager $castingManager = null): GenericHydrator
    {
        $this->castingManager = $castingManager;

        return $this;
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
     * @return mixed|GenericEntity
     */
    public function hydrate(array $attributes = [])
    {
        $attributes = Arr::renameKeys($attributes, $this->mapperConfig->getColumnAttributeMap());
        $attributes = $this->castingManager
            ->castArray($attributes, $this->mapperConfig->getCasts());

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
        $data = $this->castingManager
            ->castArrayForDb($data, $this->mapperConfig->getCasts());

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
