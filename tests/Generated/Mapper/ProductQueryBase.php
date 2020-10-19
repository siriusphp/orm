<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Mapper;

use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Collection\PaginatedCollection;
use Sirius\Orm\Query;
use Sirius\Orm\Query\SoftDeleteTrait;
use Sirius\Orm\Query\TimestampsTrait;
use Sirius\Orm\Tests\Generated\Entity\Product;

abstract class ProductQueryBase extends Query
{
    use TimestampsTrait;
    use SoftDeleteTrait;

    protected $createdAtColumn = 'created_on';
    protected $updatedAtColumn = 'updated_on';
    protected $deletedAtColumn = 'deleted_on';

    public function first(): ?Product
    {
        return parent::first();
    }

    /**
     * @return Collection|Product[]
     */
    public function get(): Collection
    {
        return parent::get();
    }

    /**
     * @return PaginatedCollection|Product[]
     */
    public function paginate(int $perPage, int $page = 1): PaginatedCollection
    {
        return parent::paginate($perPage, $page);
    }

    protected function init()
    {
        parent::init();
        $this->initSoftDelete();
    }
}
