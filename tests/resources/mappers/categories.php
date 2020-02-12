<?php

use Sirius\Orm\MapperConfig;

return MapperConfig::make([
    MapperConfig::TABLE     => 'categories',
    MapperConfig::COLUMNS   => ['id', 'parent_id', 'name'],
    MapperConfig::RELATIONS => [

    ]
]);