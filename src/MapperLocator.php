<?php
declare(strict_types=1);

namespace Sirius\Orm;

interface MapperLocator
{
    public function has($name): bool;

    public function get($name): Mapper;
}
