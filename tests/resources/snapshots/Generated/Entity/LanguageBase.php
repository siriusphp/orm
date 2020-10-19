<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Entity;

use Sirius\Orm\Entity\GenericEntity;

/**
 * @property int $id
 * @property string $content_type
 * @property int $content_id
 * @property string $lang
 * @property string $title
 * @property string $slug
 * @property string $description
 */
abstract class LanguageBase extends GenericEntity
{
    protected function castIdAttribute($value)
    {
        return $value === null ? $value : intval($value);
    }

    protected function castContentIdAttribute($value)
    {
        return $value === null ? $value : intval($value);
    }
}
