<?php
declare(strict_types=1);

namespace Sirius\Orm\Definition\Relation;

use Sirius\Orm\Relation\RelationConfig;

class OneToMany extends OneToOne
{
    protected $type = RelationConfig::TYPE_ONE_TO_MANY;

    protected $aggregates = [];

    public function addAggregate($name, $aggregate)
    {
        $this->aggregates[$name] = $aggregate;

        return $this;
    }
}
