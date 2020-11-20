<?php
declare(strict_types=1);

namespace Sirius\Orm\Contract;

interface MapperLocatorInterface
{
    public function get($name);
}
