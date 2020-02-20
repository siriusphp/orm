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
        $attributes = $this->mapColumnsToAttributes($attributes);
        $class      = $this->mapper->getEntityClass() ?? GenericEntity::class;

        return new $class($attributes, $this->orm->getCastingManager());
    }

    protected function mapColumnsToAttributes($arr)
    {
        $map = $this->mapper->getColumnAttributeMap();
        if (empty($map)) {
            return $arr;
        }

        foreach ($map as $column => $attribute) {
            if (isset($arr[$column])) {
                $arr[$attribute] = $arr[$column];
                unset($arr[$column]);
            }
        }

        return $arr;
    }

    public function extract(EntityInterface $entity)
    {
        $data = $this->mapAttributesToColumns($entity->getArrayCopy());

        return Arr::only($data, $this->mapper->getColumns());
    }

    public function mapAttributesToColumns($arr)
    {
        $map = $this->mapper->getColumnAttributeMap();
        if (empty($map)) {
            return $arr;
        }

        foreach ($map as $column => $attribute) {
            if (isset($arr[$attribute])) {
                $arr[$column] = $arr[$attribute];
                unset($arr[$attribute]);
            }
        }

        return $arr;
    }
}
