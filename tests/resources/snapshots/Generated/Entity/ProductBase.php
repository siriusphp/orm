<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Entity;

use DateTime;
use Sirius\Orm\Entity\GenericEntity;

/**
 * @property int $id
 * @property string $sku
 * @property float $value
 * @property array $attributes
 * @property DateTime|null $created_on
 * @property DateTime|null $updated_on
 * @property DateTime|null $deleted_on
 * @property float|null $discounted_price
 */
abstract class ProductBase extends GenericEntity
{
    protected function castIdAttribute($value)
    {
        return $value === null ? $value : intval($value);
    }

    protected function castValueAttribute($value)
    {
        return $value === null ? $value : round((float)$value, 2);
    }

    protected function castAttributesAttribute($value)
    {
        return $value === null ? $value : (is_array($value) ? $value : \json_decode($value, true));
    }

    protected function castCreatedOnAttribute($value)
    {
        return !$value ? null : (($value instanceof DateTime) ? $value : new DateTime($value));
    }

    protected function castUpdatedOnAttribute($value)
    {
        return !$value ? null : (($value instanceof DateTime) ? $value : new DateTime($value));
    }

    protected function castDeletedOnAttribute($value)
    {
        return !$value ? null : (($value instanceof DateTime) ? $value : new DateTime($value));
    }

    protected function setDiscountedPriceAttribute($value)
    {
        $this->value = $value / 0.9;
    }

    protected function getDiscountedPriceAttribute()
    {
        return round($this->value * 0.9, 2);
    }
}
