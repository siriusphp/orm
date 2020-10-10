<?php
declare(strict_types=1);

namespace Sirius\Orm\Behaviour;

use Sirius\Orm\Action\Delete;
use Sirius\Orm\Mapper;
use Sirius\Orm\Query;

class SoftDelete implements BehaviourInterface
{

    /**
     * @var string
     */
    protected $column;

    public function __construct($column = 'deleted_at')
    {
        $this->column = $column;
    }

    public function getName()
    {
        return 'soft_delete';
    }

    public function onNewDeleteAction(Mapper $mapper, Delete $delete)
    {
        return new \Sirius\Orm\Action\SoftDelete($mapper, $delete->getEntity(), ['deleted_at_column' => $this->column]);
    }

    public function onNewQuery(/** @scrutinizer ignore-unused */ Mapper $mapper, Query $query)
    {
        $query->setGuards([
            $this->column => null
        ]);

        return $query;
    }
}
