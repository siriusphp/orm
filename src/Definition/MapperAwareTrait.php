<?php
declare(strict_types=1);

namespace Sirius\Orm\Definition;

trait MapperAwareTrait
{
    /**
     * @var Mapper
     */
    protected $mapper;

    /**
     * @param Mapper $mapper
     *
     * @return self
     */
    public function setMapper(Mapper $mapper): self
    {
        $this->mapper = $mapper;

        return $this;
    }
}
