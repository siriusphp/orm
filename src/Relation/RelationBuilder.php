<?php
declare(strict_types=1);

namespace Sirius\Orm\Relation;

use Sirius\Orm\Helpers\Str;
use Sirius\Orm\Mapper;
use Sirius\Orm\Orm;

class RelationBuilder
{
    public function build(Orm $orm, Mapper $mapper, string $name, array $options): Relation
    {
        $foreignMapper = $options[RelationConfig::FOREIGN_MAPPER];
        if ($orm->has($foreignMapper)) {
            if (! $foreignMapper instanceof Mapper) {
                $foreignMapper = $orm->get($foreignMapper);
            }
        }
        $type          = $options[RelationConfig::TYPE];
        $relationClass = 'Sirius\\Orm\\Relation\\' . Str::className($type);

        if (! class_exists($relationClass)) {
            throw new InvalidArgumentException("{$relationClass} does not exist");
        }

        return new $relationClass($name, $mapper, $foreignMapper, $options);
    }
}
