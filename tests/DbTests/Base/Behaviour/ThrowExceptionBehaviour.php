<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\DbTests\Base\Behaviour;

use Sirius\Orm\Behaviour\BehaviourInterface;
use Sirius\Orm\Contract\ActionInterface;
use Sirius\Orm\DynamicMapper;
use Sirius\Orm\Tests\DbTests\Base\Action\ThrowExceptionOnRun;

class ThrowExceptionBehaviour implements BehaviourInterface
{
    public function getName()
    {
        return 'fake';
    }

    public function attachToMapper(DynamicMapper $mapper)
    {
    }

    public function onNewDeleteAction(DynamicMapper $mapper, ActionInterface $delete)
    {
        $delete->prepend(new ThrowExceptionOnRun());

        return $delete;
    }

    public function onNewSaveAction(DynamicMapper $mapper, ActionInterface $delete)
    {
        $delete->append(new ThrowExceptionOnRun());

        return $delete;
    }

}
