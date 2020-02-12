<?php

use Sirius\Orm\MapperConfig;
use Sirius\Orm\Relation\RelationOption;

return MapperConfig::make([
    MapperConfig::TABLE     => 'categories',
    MapperConfig::COLUMNS   => ['id', 'parent_id', 'name'],
    MapperConfig::RELATIONS => [
        'products' => [
            RelationOption::TYPE           => RelationOption::TYPE_ONE_TO_MANY,
            RelationOption::FOREIGN_MAPPER => 'products'
        ]
    ]
]);