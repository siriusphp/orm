<?php
declare(strict_types=1);

namespace Sirius\Orm\Definition;

abstract class Base
{

    static function make()
    {
        return new static;
    }

    public function isValid()
    {
        return count($this->getErrors()) === 0;
    }

    public function getErrors(): array
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


