<?php
declare(strict_types=1);

namespace Sirius\Orm\Blueprint;

abstract class Base
{
    public function isValid()
    {
        return count($this->getErrors()) === 0;
    }

    public function getErrors(): array
    {
        return [];
    }

    /**
     * These are observer instances that will make changes to the
     * code being generated
     *
     * @return array
     */
    public function getObservers(): array
    {
        return [];
    }

    protected function getClassConstants()
    {
        $reflect = new \ReflectionClass(get_class($this));

        return $reflect->getConstants();
    }

    public function getConstantByValue($val)
    {
        $constants = array_reverse($this->getClassConstants());

        return $constants[$val] ?? null;
    }
}
