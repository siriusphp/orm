<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

use Sirius\Orm\Helpers\Arr;
use Sirius\Orm\Mapper;
use Sirius\Orm\Orm;

class GenericEntityHydrator implements HydratorInterface
{
    /**
     * @var Orm
     */
    protected $orm;

    /**
     * @var Mapper
     */
    protected $mapper;

    public function __construct(Orm $orm, Mapper $mapper)
    {
        $this->orm    = $orm;
        $this->mapper = $mapper;
    }

    public function hydrate($attributes = [])
    {
        $attributes = $this->orm
            ->getCastingManager()
            ->castArray($attributes, $this->mapper->getCasts());
        $attributes = Arr::renameKeys($attributes, $this->mapper->getColumnAttributeMap());
        $class      = $this->mapper->getEntityClass() ?? GenericEntity::class;

        return new $class($attributes, $this->orm->getCastingManager());
    }

    public function extract(EntityInterface $entity)
    {
        $data = Arr::renameKeys(
            $entity->getArrayCopy(),
            array_flip($this->mapper->getColumnAttributeMap())
        );
        $data = $this->orm
            ->getCastingManager()
            ->castArrayForDb($data, $this->mapper->getCasts());

        return Arr::only($data, $this->mapper->getColumns());
    }
}
