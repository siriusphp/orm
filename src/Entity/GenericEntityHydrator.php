<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

use Sirius\Orm\CastingManager;
use Sirius\Orm\Helpers\Arr;
use Sirius\Orm\MapperConfig;

class GenericEntityHydrator implements HydratorInterface
{
    /**
     * @var CastingManager
     */
    protected $castingManager;

    /**
     * @var MapperConfig
     */
    protected $mapperConfig;

    public function __construct(MapperConfig $mapperConfig, CastingManager $castingManager)
    {
        $this->mapperConfig   = $mapperConfig;
        $this->castingManager = $castingManager;
    }

    public function hydrate(array $attributes = [])
    {
        $attributes = Arr::renameKeys($attributes, $this->mapperConfig->getColumnAttributeMap());
        $attributes = $this->castingManager
            ->castArray($attributes, $this->mapperConfig->getCasts());

        $class      = $this->mapperConfig->getEntityClass() ?? GenericEntity::class;

        return new $class($attributes);
    }

    public function extract(EntityInterface $entity)
    {
        $data = Arr::renameKeys(
            $entity->getArrayCopy(),
            array_flip($this->mapperConfig->getColumnAttributeMap())
        );
        $data = $this->castingManager
            ->castArrayForDb($data, $this->mapperConfig->getCasts());

        return Arr::only($data, $this->mapperConfig->getColumns());
    }

    public function get($entity, $attribute)
    {
        return $entity->{$attribute};
    }

    public function set($entity, $attribute, $value)
    {
        return $entity->{$attribute} = $value;
    }

    public function getPk($entity)
    {
        return $this->get($entity, $this->mapperConfig->getPrimaryKey());
    }

    public function setPk($entity, $value)
    {
        return $this->set($entity, $this->mapperConfig->getPrimaryKey(), $value);
    }
}
