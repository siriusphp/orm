<?php
declare(strict_types=1);

namespace Sirius\Orm\Blueprint;

use Nette\PhpGenerator\ClassType;

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

    public function observeMapperConfig(array $config):array {
        return $config;
    }

    public function observeBaseMapperClass(ClassType $class): ClassType {
        return $class;
    }

    public function observeBaseEntityClass(ClassType $class): ClassType {
        return $class;
    }

    public function observeBaseQueryClass(ClassType $class): ClassType {
        return $class;
    }
}


