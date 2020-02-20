<?php

use Sirius\Orm\MapperConfig;
use Sirius\Orm\Relation\RelationConfig;

return MapperConfig::fromArray([
    MapperConfig::TABLE     => 'images',
    MapperConfig::COLUMNS   => ['id', 'name', 'folder'],
    MapperConfig::RELATIONS => [
        'products_where_featured' => [
            RelationConfig::TYPE           => RelationConfig::TYPE_ONE_TO_MANY,
            RelationConfig::FOREIGN_MAPPER => 'products',
            RelationConfig::FOREIGN_KEY    => 'featured_image_id'
        ]
    ]
]);