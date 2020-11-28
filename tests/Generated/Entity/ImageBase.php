<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Entity;

use Sirius\Orm\Entity\GenericEntity;

/**
 * @property int $id
 * @property string $imageable_type
 * @property int $imageable_id
 * @property string $path
 * @property array $title
 * @property array|null $description
 */
abstract class ImageBase extends GenericEntity
{
    public function __construct(array $attributes = [], string $state = null)
    {
        parent::__construct($attributes, $state);
    }

    protected function castIdAttribute($value)
    {
        return $value === null ? $value : intval($value);
    }

    protected function castImageableIdAttribute($value)
    {
        return $value === null ? $value : intval($value);
    }

    protected function castTitleAttribute($value)
    {
        return $value === null ? $value : (is_array($value) ? $value : \json_decode($value, true));
    }

    protected function castDescriptionAttribute($value)
    {
        return $value === null ? $value : (is_array($value) ? $value : \json_decode($value, true));
    }
}
