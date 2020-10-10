<?php

use Sirius\Orm\MapperConfig;
use Sirius\Orm\Query;
use Sirius\Orm\Relation\RelationConfig;

return [
    MapperConfig::TABLE     => 'content_products',
    MapperConfig::PRIMARY_KEY=> 'content_id',
    MapperConfig::COLUMNS   => ['content_id', 'sku', 'price', 'category_id', 'featured_image_id'],
    MapperConfig::CASTS     => [
        'content_id'        => 'int',
        'category_id'       => 'int',
        'featured_image_id' => 'int',
        'price'             => 'decimal:2',
    ],
    MapperConfig::RELATIONS => [
        'category'       => [
            RelationConfig::FOREIGN_MAPPER => 'categories',
            RelationConfig::TYPE           => RelationConfig::TYPE_MANY_TO_ONE
        ],
        'featured_image'       => [
            RelationConfig::FOREIGN_MAPPER => 'images',
            RelationConfig::TYPE           => RelationConfig::TYPE_MANY_TO_ONE
        ],
    ]
];
