<?php

use Sirius\Orm\MapperConfig;
use Sirius\Orm\Query;
use Sirius\Orm\Relation\RelationConfig;

return MapperConfig::fromArray([
    MapperConfig::TABLE     => 'content',
    MapperConfig::COLUMNS   => ['id', 'content_type', 'title', 'description', 'summary'],
    MapperConfig::GUARDS    => ['content_type' => 'product'],
    MapperConfig::RELATIONS => [
        'category'       => [
            RelationConfig::FOREIGN_MAPPER => 'categories',
            RelationConfig::TYPE           => RelationConfig::TYPE_MANY_TO_ONE
        ],
        'fields' => [
            RelationConfig::FOREIGN_MAPPER => 'content_products',
            RelationConfig::TYPE           => RelationConfig::TYPE_ONE_TO_ONE,
            RelationConfig::FOREIGN_KEY    => 'content_id',
            RelationConfig::LOAD_STRATEGY  => RelationConfig::LOAD_EAGER
        ],
        'images'         => [
            RelationConfig::FOREIGN_MAPPER => 'images',
            RelationConfig::TYPE           => RelationConfig::TYPE_ONE_TO_MANY,
            RelationConfig::FOREIGN_GUARDS => ['type' => 'product'],
            RelationConfig::AGGREGATES     => [
                'images_count' => [
                    RelationConfig::AGG_FUNCTION => 'count(id)',
                ]
            ]
        ],
        'tags'           => [
            RelationConfig::FOREIGN_MAPPER  => 'tags',
            RelationConfig::TYPE            => RelationConfig::TYPE_MANY_TO_MANY,
            RelationConfig::THROUGH_TABLE   => 'products_tags',
            RelationConfig::THROUGH_NATIVE_COLUMN   => 'product_id',
            RelationConfig::THROUGH_COLUMNS => ['position'],
            RelationConfig::QUERY_CALLBACK  => function (Query $query) {
                $query->orderBy('position ASC');
                return $query;
            },
            RelationConfig::AGGREGATES     => [
                'tags_count' => [
                    RelationConfig::AGG_FUNCTION => 'count(tags.id)',
                ]
            ]
        ]
    ]
]);