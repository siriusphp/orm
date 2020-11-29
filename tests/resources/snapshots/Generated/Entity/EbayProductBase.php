<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Entity;

use Sirius\Orm\Entity\ClassMethodsEntity;

abstract class EbayProductBase extends ClassMethodsEntity
{
    public function __construct(array $attributes = [], string $state = null)
    {
        parent::__construct($attributes, $state);
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

    protected function castProductIdAttribute($value)
    {
        return $value === null ? $value : intval($value);
    }

    public function setProductId(?int $value)
    {
        $this->set('product_id', $value);
    }

    public function getProductId(): ?int
    {
        return $this->get('product_id');
    }

    protected function castPriceAttribute($value)
    {
        return $value === null ? $value : round((float)$value, 2);
    }

    public function setPrice(float $value)
    {
        $this->set('price', $value);
    }

    public function getPrice(): float
    {
        return $this->get('price');
    }

    public function setIsActive(bool $value)
    {
        $this->set('is_active', $value);
    }

    public function getIsActive(): bool
    {
        return $this->get('is_active');
    }

    public function setExpectedProfit(float $value)
    {
        return $this->setPrice($value / 0.3);
    }

    public function getExpectedProfit(): float
    {
        return $this->price * 0.3;
    }

    protected function castProductAttribute($value)
    {
        if ($value === null) {
            return $value;
        }

        return $value instanceOf Product ? $value : new Product((array) $value);
    }

    public function setProduct(?Product $value)
    {
        $this->set('product', $value);
    }

    public function getProduct(): ?Product
    {
        return $this->get('product');
    }
}
