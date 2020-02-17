<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

use Sirius\Orm\Entity\Tracker;

interface FactoryInterface
{
    public function newEntity($attributes = []);
}
