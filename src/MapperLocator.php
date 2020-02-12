<?php
declare(strict_types=1);

namespace Sirius\Orm;

interface MapperLocator
{
    public function has($mapperName): bool;

    public function get($mapperName): Mapper;
}
