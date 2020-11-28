<?php
declare(strict_types=1);

namespace Sirius\Orm\Blueprint;

abstract class Behaviour extends Base
{

    /**
     * @var Mapper
     */
    protected $mapper;

    /**
     * Get name of the behaviour
     *
     * @return string
     */
    abstract function getName(): string;

    abstract public function setMapper(Mapper $mapper);
}
