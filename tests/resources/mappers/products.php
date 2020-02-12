<?php

use Sirius\Orm\MapperConfig;
use Sirius\Orm\Relation\RelationOption;

return MapperConfig::make([
    MapperConfig::TABLE     => 'products',
    MapperConfig::COLUMNS   => ['id', 'category_id', 'sku', 'price'],
    MapperConfig::RELATIONS => [
        'category'       => [
            RelationOption::FOREIGN_MAPPER => 'categories',
            RelationOption::TYPE           => RelationOption::TYPE_MANY_TO_ONE
        ],
        'featured_image' => [
            RelationOption::FOREIGN_MAPPER => 'images',
            RelationOption::TYPE           => RelationOption::TYPE_ONE_TO_ONE,
            RelationOption::FOREIGN_GUARDS => ['type' => 'product'],
            RelationOption::CASCADE        => true
        ]
    ]
]);