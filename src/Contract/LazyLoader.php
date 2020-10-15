<?php
declare(strict_types=1);

namespace Sirius\Orm\Contract;

interface LazyLoader
{
    public function getForEntity($entity);
}
