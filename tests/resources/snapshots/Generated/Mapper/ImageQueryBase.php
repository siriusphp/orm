<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Mapper;

use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Collection\PaginatedCollection;
use Sirius\Orm\Query;
use Sirius\Orm\Tests\Generated\Entity\Image;

abstract class ImageQueryBase extends Query
{
    public function first(): ?Image
    {
        return parent::first();
    }

    /**
     * @return Collection|Image[]
     */
    public function get(): Collection
    {
        return parent::get();
    }

    /**
     * @return PaginatedCollection|Image[]
     */
    public function paginate(int $perPage, int $page = 1): PaginatedCollection
    {
        return parent::paginate($perPage, $page);
    }
}
