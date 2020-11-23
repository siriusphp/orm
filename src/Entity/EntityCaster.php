<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;


use Sirius\Orm\Orm;

class EntityCaster
{
    public function __construct(Orm $orm, string $name)
    {
        $this->orm  = $orm;
        $this->name = $name;
    }

    public function __invoke($value)
    {
        return $this->orm->get($this->name)->newEntity($value);
    }
}
