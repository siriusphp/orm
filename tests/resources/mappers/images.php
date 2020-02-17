<?php

use Sirius\Orm\MapperConfig;
use Sirius\Orm\Relation\RelationOption;

return MapperConfig::fromArray([
    MapperConfig::TABLE     => 'images',
    MapperConfig::COLUMNS   => ['id', 'name', 'folder'],
    MapperConfig::RELATIONS => [
        'products_where_featured' => [
            RelationOption::TYPE           => RelationOption::TYPE_ONE_TO_MANY,
            RelationOption::FOREIGN_MAPPER => 'products',
            RelationOption::FOREIGN_KEY    => 'featured_image_id'
        ]
    ]
]);