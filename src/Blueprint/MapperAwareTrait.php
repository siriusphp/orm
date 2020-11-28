<?php
declare(strict_types=1);

namespace Sirius\Orm\Blueprint;

trait MapperAwareTrait
{
    /**
     * @var Mapper
     */
    protected $mapper;

    public function setMapper(Mapper $mapper): self
    {
        $this->mapper = $mapper;

        return $this;
    }

    public function getMapper(): Mapper
    {
        return $this->mapper;
    }
}
