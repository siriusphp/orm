<?php

use Sirius\Orm\MapperConfig;
use Sirius\Orm\Query;
use Sirius\Orm\Relation\RelationConfig;

return MapperConfig::fromArray([
    MapperConfig::TABLE     => 'products',
    MapperConfig::COLUMNS   => ['id', 'category_id', 'sku', 'price'],
    MapperConfig::RELATIONS => [
        'category'       => [
            RelationConfig::FOREIGN_MAPPER => 'categories',
            RelationConfig::TYPE           => RelationConfig::TYPE_MANY_TO_ONE
        ],
        // not a REAL one-to-one relation this is just for testing
        'featured_image' => [
            RelationConfig::FOREIGN_MAPPER => 'images',
            RelationConfig::TYPE           => RelationConfig::TYPE_ONE_TO_ONE,
            RelationConfig::FOREIGN_KEY    => 'id'
        ],
//        'images'         => [
//            RelationOption::FOREIGN_MAPPER => 'images',
//            RelationOption::TYPE           => RelationOption::TYPE_ONE_TO_MANY,
//            RelationOption::FOREIGN_GUARDS => ['type' => 'product'],
//        ],
        'tags'           => [
            RelationConfig::FOREIGN_MAPPER  => 'tags',
            RelationConfig::TYPE            => RelationConfig::TYPE_MANY_TO_MANY,
            RelationConfig::THROUGH_COLUMNS => ['position'],
            RelationConfig::QUERY_CALLBACK  => function (Query $query) {
                $query->orderBy('position ASC');
                return $query;
            }
        ]
    ]
]);