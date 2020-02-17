<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Entity;

use Sirius\Orm\Entity\GenericEntity;

class ProductEntity extends GenericEntity
{

    protected $casts = [
        'category_id' => 'int',
        'value'       => 'decimal:2'
    ];

    protected function castFeaturedImageIdAttribute($value)
    {
        return (int)$value;
    }

}