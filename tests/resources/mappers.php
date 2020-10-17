<?php

use Sirius\Orm\MapperConfig;
use Sirius\Orm\Relation\RelationConfig;

return [
    'languages' => [
        MapperConfig::TABLE   => 'tbl_languages',
        MapperConfig::COLUMNS => ['id', 'content_type', 'content_id', 'lang', 'title', 'slug', 'description'],
        MapperConfig::CASTS   => [
            'id'         => 'int',
            'content_id' => 'int',
        ]
    ],
    'products'  => [
        MapperConfig::TABLE     => 'tbl_products',
        MapperConfig::COLUMNS   => ['id', 'created_on', 'updated_on', 'deleted_on', 'sku', 'price', 'attributes'],
        MapperConfig::CASTS     => [
            'id'         => 'int',
            'sku'        => 'decimal:2',
            'attributes' => 'json'
        ],
        MapperConfig::RELATIONS => [
            'languages' => [
                RelationConfig::TYPE           => RelationConfig::TYPE_ONE_TO_MANY,
                RelationConfig::FOREIGN_MAPPER => 'product_languages',
            ],
            'images'    => [
                RelationConfig::TYPE           => RelationConfig::TYPE_ONE_TO_MANY,
                RelationConfig::FOREIGN_MAPPER => 'images',
                RelationConfig::FOREIGN_KEY    => 'imageable_id',
                RelationConfig::FOREIGN_GUARDS => ['imageable_type' => 'products']
            ],
            'category'  => [
                RelationConfig::TYPE           => RelationConfig::TYPE_MANY_TO_ONE,
                RelationConfig::FOREIGN_MAPPER => 'categories',
            ],
            'ebay'      => [
                RelationConfig::TYPE           => RelationConfig::TYPE_ONE_TO_ONE,
                RelationConfig::FOREIGN_MAPPER => 'ebay_products',
            ],
            'tags'      => [
                RelationConfig::TYPE                => RelationConfig::TYPE_MANY_TO_MANY,
                RelationConfig::FOREIGN_MAPPER      => 'tags',
                RelationConfig::THROUGH_TABLE       => 'tbl_links_to_tags',
                RelationConfig::THROUGH_TABLE_ALIAS => 'products_to_tags',
                RelationConfig::THROUGH_GUARDS      => ['tagable_type' => 'products'],
                RelationConfig::THROUGH_COLUMNS     => ['position' => 'position_in_product'],
                RelationConfig::AGGREGATES     => [
                    'tags_count' => [
                        RelationConfig::AGG_FUNCTION => 'count(tags.id)',
                    ]
                ]
            ],
        ]
    ],

    'ebay_products' => [
        MapperConfig::TABLE   => 'tbl_ebay_products',
        MapperConfig::COLUMNS => ['id', 'product_id', 'price', 'is_active'],
        MapperConfig::CASTS   => [
            'id'         => 'int',
            'product_id' => 'int',
            'price'      => 'decimal:2',
            'is_active'  => 'bool'
        ]
    ],

    'product_languages' => [
        MapperConfig::TABLE   => 'tbl_languages',
        MapperConfig::GUARDS  => ['content_type' => 'products'],
        MapperConfig::COLUMNS => ['id', 'content_type', 'content_id', 'lang', 'title', 'slug', 'description'],
        MapperConfig::CASTS   => [
            'id'         => 'int',
            'content_id' => 'int',
        ]
    ],

    'images' => [
        MapperConfig::TABLE   => 'tbl_images',
        MapperConfig::GUARDS  => ['content_type' => 'products'],
        MapperConfig::COLUMNS => ['id', 'imageable_type', 'imageable_id', 'path', 'title', 'description'],
        MapperConfig::CASTS   => [
            'id'           => 'int',
            'imageable_id' => 'int',
        ]
    ],

    'tags' => [
        MapperConfig::TABLE   => 'tags',
        MapperConfig::COLUMNS => ['id', 'name'],
        MapperConfig::CASTS   => [
            'id' => 'int',
        ]
    ],

    'categories' => [
        MapperConfig::TABLE     => 'categories',
        MapperConfig::COLUMNS   => ['id', 'position', 'name'],
        MapperConfig::CASTS     => [
            'id'       => 'int',
            'position' => 'int',
        ],
        MapperConfig::RELATIONS => [
            'parent'    => [
                RelationConfig::TYPE           => RelationConfig::TYPE_MANY_TO_ONE,
                RelationConfig::FOREIGN_MAPPER => 'categories',
            ],
            'children'  => [
                RelationConfig::TYPE           => RelationConfig::TYPE_ONE_TO_MANY,
                RelationConfig::FOREIGN_MAPPER => 'categories',
                RelationConfig::FOREIGN_KEY    => 'parent_id',
            ],
            'languages' => [
                RelationConfig::TYPE           => RelationConfig::TYPE_ONE_TO_MANY,
                RelationConfig::FOREIGN_MAPPER => 'languages',
                RelationConfig::FOREIGN_GUARDS => ['content_type' => 'categories']
            ],
            'products' => [
                RelationConfig::TYPE           => RelationConfig::TYPE_ONE_TO_MANY,
                RelationConfig::FOREIGN_MAPPER => 'categories',
                RelationConfig::AGGREGATES => [
                    'lowest_price' => [
                        RelationConfig::AGG_FUNCTION => 'min(products.price)',
                    ],
                    'highest_price' => [
                        RelationConfig::AGG_FUNCTION => 'max(products.price)',
                    ],
                ]
            ]
        ]
    ],
];
