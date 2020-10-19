<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Mapper;

use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Collection\PaginatedCollection;
use Sirius\Orm\Query;
use Sirius\Orm\Tests\Generated\Entity\EbayProduct;

abstract class EbayProductQueryBase extends Query
{
    public function first(): ?EbayProduct
    {
        return parent::first();
    }

    /**
     * @return Collection|EbayProduct[]
     */
    public function get(): Collection
    {
        return parent::get();
    }

    /**
     * @return PaginatedCollection|EbayProduct[]
     */
    public function paginate(int $perPage, int $page = 1): PaginatedCollection
    {
        return parent::paginate($perPage, $page);
    }
}
