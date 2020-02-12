<?php
declare(strict_types=1);

namespace Sirius\Orm\Action;

interface ActionInterface
{
    public function revert();

    public function run();

    public function onSuccess();
}
