<?php

use Sirius\Orm\Definition\Column;
use Sirius\Orm\Definition\Mapper;
use Sirius\Orm\Definition\Orm;
use Sirius\Orm\Definition\Relation\ManyToMany;
use Sirius\Orm\Definition\Relation\ManyToOne;
use Sirius\Orm\Definition\Relation\OneToMany;

$orm = Orm::make()
          ->setMapperNamespace('Sirius\\Orm\\Tests\\Generated\\Mapper\\')
          ->setMapperDestination(__DIR__ . '/Generated/Mapper/')
          ->setEntityNamespace('Sirius\\Orm\\Tests\\Generated\\Entity\\')
          ->setEntityDestination(__DIR__ . '/Generated/Entity/');

$orm->addMapper(
    'products',
    Mapper::make()
          ->setTable('tbl_products')
        // columns
          ->addAutoIncrementColumn()
          ->addColumn('name', Column::varchar())
          ->addColumn('slug', Column::varchar()->setUnique(true))
          ->addColumn('description', Column::string()->setNullable(true))
          ->addColumn('price', Column::decimal(14, 2)
                                     ->setDefault(0)
                                     ->setPreviousName('cost'))
          ->addColumn('attributes', Column::json())
        // relations
          ->addRelation('languages', OneToMany::make('product_languages'))
          ->addRelation('images', OneToMany::make()
                                           ->setCascade(true)
                                           ->setForeignKey('imageable_id')
                                           ->setForeignGuards(['imageable_type' => 'products']))
          ->addRelation('tags', ManyToMany::make('tags')
                                          ->setThroughTable('tbl_links_to_tags')
                                          ->setThroughTableAlias('products_to_tags')
                                          ->setThroughGuards(['tagable_type' => 'products'])
                                          ->setThroughColumns(['position' => 'position_in_product']))
          ->addRelation('category', ManyToOne::make())
);

$orm->addMapper(
    'product_languages',
    Mapper::make()
          ->setTable('tbl_product_languages')
        // columns
          ->addAutoIncrementColumn()
          ->addColumn('product_id', Column::bigInteger(true)->setIndex(true))
          ->addColumn('lang', Column::varchar(5)->setIndex(true))
          ->addColumn('title', Column::string())
          ->addColumn('description', Column::string()->setNullable(true))
);

$orm->addMapper(
    'images',
    Mapper::make()
          ->setTable('tbl_images')
        // columns
          ->addAutoIncrementColumn()
          ->addColumn('imageable_id', Column::bigInteger(true)->setIndex(true))
          ->addColumn('imageable_type', Column::varchar(100)->setIndex(true))
          ->addColumn('path', Column::string())
          ->addColumn('title', Column::json())
          ->addColumn('description', Column::json()->setNullable(true))
);

$orm->addMapper(
    'tags',
    Mapper::make()
          ->addAutoIncrementColumn()
          ->addColumn('name', Column::string()->setUnique(true))
);

$orm->addMapper(
    'categories',
    Mapper::make()
        // columns
          ->addAutoIncrementColumn()
          ->addColumn('parent_id', Column::bigInteger(true)->setNullable(true)->setIndex(true))
          ->addColumn('position', Column::integer(true)->setDefault(0))
          ->addColumn('name', Column::string()->setUnique(true))
        // relations
          ->addRelation('parent', ManyToOne::make('categories'))
          ->addRelation('children', OneToMany::make('categories'))
          ->addRelation('languages', OneToMany::make('category_languages'))
);

$orm->addMapper(
    'category_languages',
    Mapper::make()
        // columns
        ->addAutoIncrementColumn()
        ->addColumn('category_id', Column::bigInteger(true)->setIndex(true))
        ->addColumn('lang', Column::varchar(5)->setIndex(true))
        ->addColumn('title', Column::string())
        ->addColumn('description', Column::string()->setNullable(true))
);

return $orm;
