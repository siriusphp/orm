<?php
declare(strict_types=1);

namespace Sirius\Orm\Contract;

use Sirius\Orm\CastingManager;

interface CastingManagerAwareInterface
{
    public function setCastingManager(CastingManager $castingManager);
}
