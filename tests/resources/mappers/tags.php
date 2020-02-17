<?php

use Sirius\Orm\MapperConfig;
use Sirius\Orm\Relation\RelationOption;

return MapperConfig::fromArray([
    MapperConfig::TABLE     => 'tags',
    MapperConfig::COLUMNS   => ['id', 'name'],
    MapperConfig::RELATIONS => [
        'products' => [
            RelationOption::FOREIGN_MAPPER  => 'products',
            RelationOption::TYPE            => RelationOption::TYPE_MANY_TO_MANY,
            RelationOption::THROUGH_COLUMNS => ['position'],
        ]
    ]
]);