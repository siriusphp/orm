<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Entity;

use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Entity\GenericEntity;

/**
 * @property int $id
 * @property int|null $parent_id
 * @property int $position
 * @property string $name
 * @property Category|null $parent
 * @property Category[]|Collection $children
 * @property Language[]|Collection $languages
 * @property Product[]|Collection $products
 */
abstract class CategoryBase extends GenericEntity
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

    protected function castParentIdAttribute($value)
    {
        return $value === null ? $value : intval($value);
    }

    protected function castPositionAttribute($value)
    {
        return $value === null ? $value : intval($value);
    }

    protected function castParentAttribute($value)
    {
        if ($value === null) {
            return $value;
        }

        return $value instanceOf Category ? $value : new Category((array) $value);
    }

    public function addChild(Category $child)
    {
        $this->get('children')->addElement($child);
    }

    public function addLanguage(Language $language)
    {
        $this->get('languages')->addElement($language);
    }

    public function addProduct(Product $product)
    {
        $this->get('products')->addElement($product);
    }
}
