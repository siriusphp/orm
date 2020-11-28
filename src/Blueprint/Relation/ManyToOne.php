<?php
declare(strict_types=1);

namespace Sirius\Orm\Blueprint\Relation;

use Sirius\Orm\Blueprint\Relation;
use Sirius\Orm\Helpers\Inflector;
use Sirius\Orm\Relation\RelationConfig;

class ManyToOne extends Relation
{
    protected $type = RelationConfig::TYPE_MANY_TO_ONE;

    protected $foreignKey = 'id';

    public function setForeignMapper($foreignMapper): Relation
    {
        if ($foreignMapper && ! $this->nativeKey) {
            $this->nativeKey = Inflector::singularize($foreignMapper) . '_id';
        }

        return parent::setForeignMapper($foreignMapper);
    }
}
