<?php


namespace Sirius\Orm\Action;

interface ActionInterface
{
    public function revert();

    public function run();
}
