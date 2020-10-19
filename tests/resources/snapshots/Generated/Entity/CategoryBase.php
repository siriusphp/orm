<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Entity;

use Sirius\Orm\Entity\GenericEntity;

/**
 * @property int $id
 * @property int $parent_id
 * @property int $position
 * @property string $name
 */
abstract class CategoryBase extends GenericEntity
{
    protected function castIdAttribute($value)
    {
        return intval($value);
    }

    protected function castParentIdAttribute($value)
    {
        return intval($value);
    }

    protected function castPositionAttribute($value)
    {
        return intval($value);
    }
}
