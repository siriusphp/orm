<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Mapper;

use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Collection\PaginatedCollection;
use Sirius\Orm\Query;
use Sirius\Orm\Tests\Generated\Entity\Language;

abstract class LanguageQueryBase extends Query
{
    public function first(): ?Language
    {
        return parent::first();
    }

    /**
     * @return Collection|Language[]
     */
    public function get(): Collection
    {
        return parent::get();
    }

    /**
     * @return PaginatedCollection|Language[]
     */
    public function paginate(int $perPage, int $page = 1): PaginatedCollection
    {
        return parent::paginate($perPage, $page);
    }
}
