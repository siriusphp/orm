<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Mapper;

use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Collection\PaginatedCollection;
use Sirius\Orm\Query;
use Sirius\Orm\Tests\Generated\Entity\ProductLanguage;

abstract class ProductLanguageQueryBase extends Query
{
    public function first(): ?ProductLanguage
    {
        return parent::first();
    }

    /**
     * @return Collection|ProductLanguage[]
     */
    public function get(): Collection
    {
        return parent::get();
    }

    /**
     * @return PaginatedCollection|ProductLanguage[]
     */
    public function paginate(int $perPage, int $page = 1): PaginatedCollection
    {
        return parent::paginate($perPage, $page);
    }
}
