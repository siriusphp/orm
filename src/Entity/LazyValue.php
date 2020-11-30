<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

use Sirius\Orm\Contract\LazyLoader;

class LazyValue implements LazyLoader
{
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getForEntity($entity)
    {
        return $this->value;
    }
}
