<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;


use Sirius\Orm\Orm;

class CollectionCaster
{
    public function __construct(Orm $orm, string $name)
    {
        $this->orm  = $orm;
        $this->name = $name;
    }

    public function __invoke($value)
    {
        return $this->orm->get($this->name)->newCollection($value);
    }
}
