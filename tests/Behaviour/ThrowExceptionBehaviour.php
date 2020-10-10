<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\Behaviour;

use Sirius\Orm\Action\ActionInterface;
use Sirius\Orm\Behaviour\BehaviourInterface;
use Sirius\Orm\Mapper;
use Sirius\Orm\Tests\Action\ThrowExceptionOnRun;

class ThrowExceptionBehaviour implements BehaviourInterface
{
    public function getName()
    {
        return 'fake';
    }

    public function attachToMapper(Mapper $mapper)
    {
    }

    public function onNewDeleteAction(Mapper $mapper, ActionInterface $delete)
    {
        $delete->prepend(new ThrowExceptionOnRun());

        return $delete;
    }

    public function onNewSaveAction(Mapper $mapper, ActionInterface $delete)
    {
        $delete->append(new ThrowExceptionOnRun());

        return $delete;
    }

}
