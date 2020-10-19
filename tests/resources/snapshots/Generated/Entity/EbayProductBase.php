<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Entity;

use Sirius\Orm\Entity\GenericEntity;

/**
 * @property int $id
 * @property int $product_id
 * @property float $price
 * @property bool $is_active
 */
abstract class EbayProductBase extends GenericEntity
{
    protected function castIdAttribute($value)
    {
        return $value === null ? $value : intval($value);
    }

    protected function castProductIdAttribute($value)
    {
        return $value === null ? $value : intval($value);
    }

    protected function castPriceAttribute($value)
    {
        return $value === null ? $value : round((float)$value, 2);
    }
}
