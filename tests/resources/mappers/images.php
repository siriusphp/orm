<?php

use Sirius\Orm\MapperConfig;

return MapperConfig::make([
    MapperConfig::TABLE     => 'images',
    MapperConfig::COLUMNS   => ['id', 'name', 'folder'],
    MapperConfig::RELATIONS => [

    ]
]);