<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

interface LazyLoader
{
    public function load($entity);
}
