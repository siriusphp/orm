<?php
declare(strict_types=1);

namespace Sirius\Orm\Definition;

abstract class Behaviour extends Base
{
    /**
     * Get name of the behaviour
     *
     * @return string
     */
    abstract function getName(): string;
}
