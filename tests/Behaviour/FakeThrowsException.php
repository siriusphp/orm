<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Behaviour;

use Sirius\Orm\Behaviour\BehaviourInterface;
use Sirius\Orm\Mapper;
use Sirius\Orm\Action\ActionInterface;

class FakeThrowsException implements BehaviourInterface
{
    public function getName() {
        return 'fake';
    }

    public function onDelete(Mapper $mapper, ActionInterface $delete)
    {
        $delete->prepend(new \Sirius\Orm\Tests\Action\FakeThrowsException());
        return $delete;
    }

}