<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

use Sirius\Orm\Mapper;
use Sirius\Orm\Orm;

class GenericEntityFactory implements FactoryInterface
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

    public function newEntity($attributes = [])
    {
        $attributes = $this->mapColumnsToAttributes($attributes);
        $class = $this->mapper->getEntityClass() ?? GenericEntity::class;

        return new $class($attributes);
    }

    protected function mapColumnsToAttributes($attributes)
    {
        $map = $this->mapper->getColumnAttributeMap();
        if (empty($map)) {
            return $attributes;
        }

        foreach ($map as $column => $attribute) {
            if (isset($attributes[$column])) {
                $attributes[$attribute] = $attributes[$column];
                unset($attributes[$column]);
            }
        }

        return $attributes;
    }
}
