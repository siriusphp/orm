<?php
declare(strict_types=1);

namespace Sirius\Orm\Definition\Relation;

use Sirius\Orm\Definition\Mapper;
use Sirius\Orm\Definition\Relation;
use Sirius\Orm\Helpers\Inflector;
use Sirius\Orm\Relation\RelationConfig;

class OneToOne extends Relation
{
    protected $type = RelationConfig::TYPE_ONE_TO_ONE;

    public function setMapper(Mapper $mapper): Relation
    {
        if ( ! $this->foreignKey) {
            $this->foreignKey = Inflector::singularize($mapper->getName()) . '_id';
        }

        if ( ! $this->nativeKey) {
            $this->nativeKey = $mapper->getConfig()->getPrimaryKey();
        }

        return parent::setMapper($mapper);
    }

}