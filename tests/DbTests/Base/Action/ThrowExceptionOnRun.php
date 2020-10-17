<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\DbTests\Base\Action;

use Sirius\Orm\Contract\ActionInterface;

class ThrowExceptionOnRun implements ActionInterface
{
    public function run()
    {
        throw new \Exception();
    }

    public function revert()
    {
    }

    public function onSuccess()
    {
    }
}
