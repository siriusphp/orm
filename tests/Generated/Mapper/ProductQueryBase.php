<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Mapper;

use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Collection\PaginatedCollection;
use Sirius\Orm\Query;
use Sirius\Orm\Tests\Generated\Entity\Product;

abstract class ProductQueryBase extends Query
{
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

    public function orderByFirstCreated()
    {
        $this->orderBy($this->createdAtColumn . ' ASC');

        return $this;
    }

    public function orderByLastCreated()
    {
        $this->orderBy($this->updatedAtColumn . ' DESC');

        return $this;
    }

    public function orderByFirstUpdated()
    {
        $this->orderBy($this->updatedAtColumn . ' ASC');

        return $this;
    }

    protected function init()
    {
        parent::init();
        $this->guards[] = $this->deletedAtColumn . ' IS NULL';
    }

    public function withTrashed()
    {
        $guards = [];
        foreach ($this->guards as $k => $v) {
            if ($v != $this->deletedAtColumn . ' IS NULL') {
                $guards[$k] = $v;
            }
        }
        $this->guards = $guards;

        return $this;
    }
}
