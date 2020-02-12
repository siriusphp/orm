<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

use Sirius\Orm\FactoryInterface;
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

    public function newInstance($attributes = [], array $load = [], Tracker $tracker = null)
    {
        $attributes = $this->processAttributes($attributes, $load, $tracker);
        $class = $this->mapper->getEntityClass() ?? GenericEntity::class;

        return new $class($attributes);
    }

    protected function processAttributes($attributes, array $load = [], Tracker $tracker = null)
    {
        $attributes = $this->mapColumnsToAttributes($attributes);

        return $attributes;
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
