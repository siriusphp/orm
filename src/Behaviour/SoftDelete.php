<?php
declare(strict_types=1);

namespace Sirius\Orm\Behaviour;

use Sirius\Orm\Action\ActionInterface;
use Sirius\Orm\Mapper;

class SoftDelete implements BehaviourInterface
{
    public function getName()
    {
        return 'soft_delete';
    }

    public function onDelete(Mapper $mapper, ActionInterface $delete)
    {
        // TODO: replace DELETE action with an SOFT_DELETE action
    }
}
