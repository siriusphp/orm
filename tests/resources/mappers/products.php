<?php

use Sirius\Orm\MapperConfig;
use Sirius\Orm\Query;
use Sirius\Orm\Relation\RelationOption;

return MapperConfig::fromArray([
    MapperConfig::TABLE     => 'products',
    MapperConfig::COLUMNS   => ['id', 'category_id', 'sku', 'price'],
    MapperConfig::RELATIONS => [
        'category'       => [
            RelationOption::FOREIGN_MAPPER => 'categories',
            RelationOption::TYPE           => RelationOption::TYPE_MANY_TO_ONE
        ],
        // not a REAL one-to-one relation this is just for testing
        'featured_image' => [
            RelationOption::FOREIGN_MAPPER => 'images',
            RelationOption::TYPE           => RelationOption::TYPE_ONE_TO_ONE,
            RelationOption::FOREIGN_KEY    => 'id'
        ],
//        'images'         => [
//            RelationOption::FOREIGN_MAPPER => 'images',
//            RelationOption::TYPE           => RelationOption::TYPE_ONE_TO_MANY,
//            RelationOption::FOREIGN_GUARDS => ['type' => 'product'],
//        ],
        'tags'           => [
            RelationOption::FOREIGN_MAPPER  => 'tags',
            RelationOption::TYPE            => RelationOption::TYPE_MANY_TO_MANY,
            RelationOption::THROUGH_COLUMNS => ['position'],
            RelationOption::QUERY_CALLBACK  => function (Query $query) {
                $query->orderBy('position ASC');
                return $query;
            }
        ]
    ]
]);