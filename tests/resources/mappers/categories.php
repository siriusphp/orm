<?php

use Sirius\Orm\MapperConfig;
use Sirius\Orm\Relation\RelationConfig;

return [
    MapperConfig::TABLE     => 'categories',
    MapperConfig::COLUMNS   => ['id', 'parent_id', 'name'],
    MapperConfig::CASTS     => [
        'id' => 'int',
        'details' => 'json'
    ],
    MapperConfig::RELATIONS => [
        'products' => [
            RelationConfig::TYPE           => RelationConfig::TYPE_ONE_TO_MANY,
            RelationConfig::FOREIGN_MAPPER => 'content_products',
            RelationConfig::AGGREGATES     => [
                'products_count' => [
                    RelationConfig::AGG_FUNCTION => 'count(content_id)',
                ]
            ]
        ],
        'parent' => [
            RelationConfig::TYPE           => RelationConfig::TYPE_MANY_TO_ONE,
            RelationConfig::FOREIGN_MAPPER => 'categories',
        ]
    ]
];
