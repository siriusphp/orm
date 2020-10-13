<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Entity;

use Sirius\Orm\Entity\GenericEntity;

/**
 * @property int $id
 * @property string $name
 */
abstract class TagBase extends GenericEntity
{
    protected function castIdAttribute($value)
    {
        return intval($value);
    }
}
