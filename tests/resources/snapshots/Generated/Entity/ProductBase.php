<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Entity;

use DateTime;
use Sirius\Orm\Entity\GenericEntity;

/**
 * @property int $id
 * @property string $sku
 * @property float $price
 * @property array $attributes
 * @property DateTime $created_on
 * @property DateTime $updated_on
 * @property DateTime $deleted_on
 * @property float $discounted_price
 */
abstract class ProductBase extends GenericEntity
{
    protected function castIdAttribute($value)
    {
        return intval($value);
    }

    protected function castPriceAttribute($value)
    {
        return round((float)$value, 2);
    }

    protected function castCreatedOnAttribute($value)
    {
        return ($value instanceof DateTime) ? $value : new DateTime($value);
    }

    protected function castUpdatedOnAttribute($value)
    {
        return ($value instanceof DateTime) ? $value : new DateTime($value);
    }

    protected function castDeletedOnAttribute($value)
    {
        return ($value instanceof DateTime) ? $value : new DateTime($value);
    }

    protected function setDiscountedPriceAttribute($value)
    {
        $this->price = $value / 0.9;
    }

    protected function getDiscountedPriceAttribute()
    {
        return round($this->price * 0.9, 2);
    }
}
