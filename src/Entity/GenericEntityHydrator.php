<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

use Sirius\Orm\CastingManager;
use Sirius\Orm\Helpers\Arr;
use Sirius\Orm\Mapper;

class GenericEntityHydrator implements HydratorInterface
{
    /**
     * @var CastingManager
     */
    protected $castingManager;

    /**
     * @var Mapper
     */
    protected $mapper;

    public function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function setCastingManager(CastingManager $castingManager)
    {
        $this->castingManager = $castingManager;
    }

    public function hydrate(array $attributes = [])
    {
        $attributes = $this->castingManager
                           ->castArray($attributes, $this->mapper->getCasts());
        $attributes = Arr::renameKeys($attributes, $this->mapper->getColumnAttributeMap());
        $class      = $this->mapper->getEntityClass() ?? GenericEntity::class;

        return new $class($attributes, $this->castingManager);
    }

    public function extract(EntityInterface $entity)
    {
        $data = Arr::renameKeys(
            $entity->getArrayCopy(),
            array_flip($this->mapper->getColumnAttributeMap())
        );
        $data = $this->castingManager
                     ->castArrayForDb($data, $this->mapper->getCasts());

        return Arr::only($data, $this->mapper->getColumns());
    }
}
