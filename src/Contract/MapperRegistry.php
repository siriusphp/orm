<?php
declare(strict_types=1);

namespace Sirius\Orm\Contract;

use Sirius\Orm\Mapper;

interface MapperRegistry
{
    public function set(string $name, Mapper $mapper);

    public function get(string $name): Mapper;

    public function has(string $name): bool;
}
