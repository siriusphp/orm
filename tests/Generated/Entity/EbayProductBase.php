<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Entity;

use Sirius\Orm\Entity\ClassMethodsEntity;

abstract class EbayProductBase extends ClassMethodsEntity
{
    protected function castIdAttribute($value)
    {
        return $value === null ? $value : intval($value);
    }

    public function setId($value)
    {
        $this->set('id', $value);
    }

    public function getId(): int
    {
        return $this->get('id');
    }

    protected function castProductIdAttribute($value)
    {
        return $value === null ? $value : intval($value);
    }

    public function setProductId($value)
    {
        $this->set('product_id', $value);
    }

    public function getProductId(): int
    {
        return $this->get('product_id');
    }

    protected function castPriceAttribute($value)
    {
        return $value === null ? $value : round((float)$value, 2);
    }

    public function setPrice($value)
    {
        $this->set('price', $value);
    }

    public function getPrice(): float
    {
        return $this->get('price');
    }

    public function setIsActive($value)
    {
        $this->set('is_active', $value);
    }

    public function getIsActive(): bool
    {
        return $this->get('is_active');
    }
}
