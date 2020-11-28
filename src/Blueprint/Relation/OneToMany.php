<?php
declare(strict_types=1);

namespace Sirius\Orm\Blueprint\Relation;

use Sirius\Orm\Blueprint\Relation;
use Sirius\Orm\Relation\RelationConfig;

class OneToMany extends OneToOne
{
    protected $type = RelationConfig::TYPE_ONE_TO_MANY;

    protected $cascade;

    protected $aggregates = [];

    /**
     * @return bool
     */
    public function getCascade()
    {
        return $this->cascade;
    }

    /**
     * @param bool $cascade
     *
     * @return Relation
     */
    public function setCascade(bool $cascade)
    {
        $this->cascade = $cascade;

        return $this;
    }

    public function addAggregate($name, $aggregate)
    {
        $this->aggregates[$name] = $aggregate;

        return $this;
    }
}
