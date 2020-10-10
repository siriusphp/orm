<?php
declare(strict_types=1);

namespace Sirius\Orm\Definition\Relation;

use Sirius\Orm\Definition\Mapper;
use Sirius\Orm\Definition\Relation;
use Sirius\Orm\Helpers\Inflector;
use Sirius\Orm\Relation\RelationConfig;

class ManyToOne extends Relation
{
    protected $type = RelationConfig::TYPE_MANY_TO_ONE;

    protected $foreignKey = 'id';

    public function setForeignMapper($foreignMapper)
    {
        if ( ! $this->nativeKey) {
            $this->nativeKey = Inflector::singularize($foreignMapper) . '_id';
        }

        return parent::setForeignMapper($foreignMapper);
    }

    public function setMapper(Mapper $mapper): Relation
    {
        $this->nativeKey = $mapper->getConfig()->getPrimaryKey();

        return parent::setMapper($mapper);
    }

}
