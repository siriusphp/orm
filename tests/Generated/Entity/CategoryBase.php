<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Entity;

use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Entity\ClassMethodsEntity;

abstract class CategoryBase extends ClassMethodsEntity
{
    public function __construct(array $attributes = [], string $state = null)
    {
        parent::__construct($attributes, $state);
        // this is a fail-safe procedure that will be executed
        // only when you use `new Entity()` instead of `$mapper->newEntity()`
        // ALWAYS try to use `$mapper->newEntity()`
        if (!isset($this->attributes['children'])) {
            $this->attributes['children'] = new Collection;
        }

        // this is a fail-safe procedure that will be executed
        // only when you use `new Entity()` instead of `$mapper->newEntity()`
        // ALWAYS try to use `$mapper->newEntity()`
        if (!isset($this->attributes['languages'])) {
            $this->attributes['languages'] = new Collection;
        }

        // this is a fail-safe procedure that will be executed
        // only when you use `new Entity()` instead of `$mapper->newEntity()`
        // ALWAYS try to use `$mapper->newEntity()`
        if (!isset($this->attributes['products'])) {
            $this->attributes['products'] = new Collection;
        }
    }

    protected function castIdAttribute($value)
    {
        return $value === null ? $value : intval($value);
    }

    public function setId(?int $value)
    {
        $this->set('id', $value);
    }

    public function getId(): ?int
    {
        return $this->get('id');
    }

    protected function castParentIdAttribute($value)
    {
        return $value === null ? $value : intval($value);
    }

    public function setParentId(?int $value)
    {
        $this->set('parent_id', $value);
    }

    public function getParentId(): ?int
    {
        return $this->get('parent_id');
    }

    protected function castPositionAttribute($value)
    {
        return $value === null ? $value : intval($value);
    }

    public function setPosition(int $value)
    {
        $this->set('position', $value);
    }

    public function getPosition(): int
    {
        return $this->get('position');
    }

    public function setName(string $value)
    {
        $this->set('name', $value);
    }

    public function getName(): string
    {
        return $this->get('name');
    }

    protected function castParentAttribute($value)
    {
        if ($value === null) {
            return $value;
        }

        return $value instanceOf Category ? $value : new Category((array) $value);
    }

    public function setParent(?Category $value)
    {
        $this->set('parent', $value);
    }

    public function getParent(): ?Category
    {
        return $this->get('parent');
    }

    public function setChildren(Collection $value)
    {
        $this->set('children', $value);
    }

    /**
     * @return Collection|Category[]
     */
    public function getChildren(): Collection
    {
        return $this->get('children');
    }

    public function addChild(Category $child)
    {
        $this->get('children')->add($child);
    }

    public function setLanguages(Collection $value)
    {
        $this->set('languages', $value);
    }

    /**
     * @return Collection|Language[]
     */
    public function getLanguages(): Collection
    {
        return $this->get('languages');
    }

    public function addLanguage(Language $language)
    {
        $this->get('languages')->add($language);
    }

    public function setProducts(Collection $value)
    {
        $this->set('products', $value);
    }

    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        return $this->get('products');
    }

    public function addProduct(Product $product)
    {
        $this->get('products')->add($product);
    }

    public function getLowestPrice()
    {
        return $this->get('lowest_price');
    }

    public function getHighestPrice()
    {
        return $this->get('highest_price');
    }
}
