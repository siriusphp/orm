<?php
declare(strict_types=1);

namespace Sirius\Orm\Action;

use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\Relation\Relation;

class AttachEntities implements ActionInterface
{
    public function __construct(
        EntityInterface $nativeEntity,
        EntityInterface $foreignEntity,
        Relation $relation,
        string $actionType
    ) {
        $this->nativeEntity = $nativeEntity;
        $this->foreignEntity = $foreignEntity;
        $this->relation = $relation;
        $this->actionType = $actionType;
    }

    public function revert()
    {
        /**
         * @todo restore previous values
         */
    }

    public function run()
    {
        /**
         * @todo store current attribute values
         */
    }

    public function onSuccess()
    {
        $this->relation->attachMatchesToEntity($this->nativeEntity, [$this->foreignEntity]);
    }
}
