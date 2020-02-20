<?php

use Sirius\Orm\MapperConfig;
use Sirius\Orm\Relation\RelationConfig;

return MapperConfig::fromArray([
    MapperConfig::TABLE     => 'categories',
    MapperConfig::COLUMNS   => ['id', 'parent_id', 'name'],
    MapperConfig::RELATIONS => [
        'products' => [
            RelationConfig::TYPE           => RelationConfig::TYPE_ONE_TO_MANY,
            RelationConfig::FOREIGN_MAPPER => 'products'
        ]
    ]
]);