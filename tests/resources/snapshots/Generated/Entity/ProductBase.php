<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Entity;

use DateTime;
use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Entity\GenericEntity;

/**
 * @property int $id
 * @property int $category_id
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
 * @property mixed $tags_count
 * @property Category|null $category
 * @property EbayProduct|null $ebay
 */
abstract class ProductBase extends GenericEntity
{
    public function __construct(array $attributes = [], string $state = null)
    {
        parent::__construct($attributes, $state);
    }

    protected function castIdAttribute($value)
    {
        return $value === null ? $value : intval($value);
    }

    protected function castCategoryIdAttribute($value)
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

    public function addLanguage(ProductLanguage $language)
    {
        $this->get('languages')->add($language);
    }

    public function addImage(Image $image)
    {
        $this->get('images')->add($image);
    }

    public function addTag(Tag $tag)
    {
        $this->get('tags')->add($tag);
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
