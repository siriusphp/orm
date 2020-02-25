<?php

use Sirius\Orm\MapperConfig;
use Sirius\Orm\Relation\RelationConfig;

return MapperConfig::fromArray([
    MapperConfig::TABLE     => 'tags',
    MapperConfig::COLUMNS   => ['id', 'name'],
//    MapperConfig::RELATIONS => [
//        'products' => [
//            RelationConfig::FOREIGN_MAPPER  => 'products',
//            RelationConfig::TYPE            => RelationConfig::TYPE_MANY_TO_MANY,
//            RelationConfig::THROUGH_COLUMNS => ['position'],
//        ]
//    ]
]);