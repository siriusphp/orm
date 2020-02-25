<?php

namespace Sirius\Orm\Relation;

use Sirius\Orm\Helpers\Str;
use Sirius\Orm\Mapper;
use Sirius\Orm\Orm;

class RelationBuilder
{

    /**
     * @var Orm
     */
    protected $orm;

    public function __construct(Orm $orm)
    {
        $this->orm = $orm;
    }

    public function newRelation(Mapper $nativeMapper, $name, $options): Relation
    {
        $foreignMapper = $options[RelationConfig::FOREIGN_MAPPER];
        if ($this->orm->has($foreignMapper)) {
            if (! $foreignMapper instanceof Mapper) {
                $foreignMapper = $this->orm->get($foreignMapper);
            }
        }
        $type          = $options[RelationConfig::TYPE];
        $relationClass = __NAMESPACE__ . '\\' . Str::className($type);

        if (! class_exists($relationClass)) {
            throw new \InvalidArgumentException("{$relationClass} does not exist");
        }

        return new $relationClass($name, $nativeMapper, $foreignMapper, $options);
    }
}
