<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Entity;

use DateTime;
use Sirius\Orm\Collection\Collection;
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
 * @property ProductLanguage[]|Collection $languages
 * @property Image[]|Collection $images
 * @property Tag[]|Collection $tags
 * @property Category|null $category
 * @property EbayProduct|null $ebay
 */
abstract class ProductBase extends GenericEntity
{
    public function __construct(array $attributes = [], string $state = null)
    {
        parent::__construct($attributes, $state);
        if (!isset($this->attributes['languages'])) {
            $this->attributes['languages'] = new Collection;
        }

        if (!isset($this->attributes['images'])) {
            $this->attributes['images'] = new Collection;
        }

        if (!isset($this->attributes['tags'])) {
            $this->attributes['tags'] = new Collection;
        }
    }

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

    protected function setDiscountedPriceAttribute(?float $value)
    {
        $this->value = $value / 0.9;
    }

    protected function getDiscountedPriceAttribute(): ?float
    {
        return round($this->value * 0.9, 2);
    }

    public function addLanguage(ProductLanguage $value)
    {
        $this->attributes['languages']->addElement($value);
    }

    public function addImage(Image $value)
    {
        $this->attributes['images']->addElement($value);
    }

    public function addTag(Tag $value)
    {
        $this->attributes['tags']->addElement($value);
    }

    protected function castCategoryAttribute($value)
    {
        if ($value === null) {
            return $value;
        }

        return $value instanceOf Category ? $value : new Category((array) $value);
    }

    protected function castEbayAttribute($value)
    {
        if ($value === null) {
            return $value;
        }

        return $value instanceOf EbayProduct ? $value : new EbayProduct((array) $value);
    }
}
