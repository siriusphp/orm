<?php
declare(strict_types=1);

namespace Sirius\Orm\CodeGenerator\Observer;

abstract class Base
{
    const PRIORITY_HIGH = 100;
    const PRIORITY_LOW = -100;

    protected $priority = 0;

    public function __construct(int $priority = 0)
    {
        $this->priority = $priority;
    }

    abstract public function observe(string $key, $object);

    /**
     * This is for loggin/debugging purposes
     * 
     * @return string
     */
    abstract public function __toString();
}
