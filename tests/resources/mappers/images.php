<?php

use Sirius\Orm\MapperConfig;
use Sirius\Orm\Relation\RelationConfig;

return [
    MapperConfig::TABLE     => 'images',
    MapperConfig::COLUMNS   => ['id', 'name', 'folder'],
    MapperConfig::CASTS     => [
        'id' => 'int',
        'content_id' => 'int'
    ],
    MapperConfig::RELATIONS => [
        'products_where_featured' => [
            RelationConfig::TYPE           => RelationConfig::TYPE_ONE_TO_MANY,
            RelationConfig::FOREIGN_MAPPER => 'products',
            RelationConfig::FOREIGN_KEY    => 'featured_image_id'
        ]
    ]
];
