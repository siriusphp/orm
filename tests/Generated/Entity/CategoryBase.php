<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Entity;

use Sirius\Orm\Entity\GenericEntity;

/**
 * @property int $id
 * @property int|null $parent_id
 * @property int $position
 * @property string $name
 */
abstract class CategoryBase extends GenericEntity
{
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
}
