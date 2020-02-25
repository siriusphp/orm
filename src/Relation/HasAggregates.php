<?php
declare(strict_types=1);

namespace Sirius\Orm\Relation;

use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\Entity\StateEnum;
use Sirius\Orm\Entity\Tracker;
use Sirius\Orm\Query;

trait HasAggregates
{
    protected $aggregates;

    protected function compileAggregates()
    {
        if (is_array($this->aggregates)) {
            return;
        }

        $aggregates = [];
        $aggregatesList = $this->getOption(RelationConfig::AGGREGATES);
        if (!is_array($aggregatesList) || empty($aggregatesList)) {
            $this->aggregates = $aggregates;
            return;
        }

        foreach ($aggregatesList as $name => $options) {
            $agg = new Aggregate($name, $this, $options);
            $aggregates[$name] = $agg;
        }

        $this->aggregates = $aggregates;
    }

    public function getAggregates()
    {
        $this->compileAggregates();

        return $this->aggregates;
    }
}
