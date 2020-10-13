<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Sirius\Orm\CodeGenerator\ClassGenerator;
use Sirius\Orm\Definition\Behaviour\SoftDelete;
use Sirius\Orm\Definition\Behaviour\Timestamps;
use Sirius\Orm\Definition\Column;
use Sirius\Orm\Definition\Mapper;
use Sirius\Orm\Definition\Orm;
use Sirius\Orm\Definition\Relation\ManyToMany;
use Sirius\Orm\Definition\Relation\ManyToOne;
use Sirius\Orm\Definition\Relation\OneToMany;
use Sirius\Orm\Definition\Relation\OneToOne;

$orm = Orm::make()
          ->setMapperNamespace('Sirius\\Orm\\Tests\\Generated\\Mapper')
          ->setMapperDestination(__DIR__ . '/../Generated/Mapper/')
          ->setEntityNamespace('Sirius\\Orm\\Tests\\Generated\\Entity')
          ->setEntityDestination(__DIR__ . '/../Generated/Entity/');

$orm->addMapper(
    Mapper::make('products')
          ->setTable('tbl_products')
        // columns
          ->addAutoIncrementColumn()
          #->addColumn(Column::datetime('created_on')->setNullable(true))
          #->addColumn(Column::datetime('updated_on')->setNullable(true))
          #->addColumn(Column::datetime('deleted_on')->setNullable(true))
          ->addColumn(Column::varchar('name'))
          ->addColumn(Column::varchar('slug')->setUnique(true))
          ->addColumn(Column::string('description')->setNullable(true))
          ->addColumn(Column::decimal('price', 14, 2)
                            ->setDefault(0)
                            ->setPreviousName('cost'))
          ->addColumn(Column::json('attributes'))
        // relations
          ->addRelation('languages', OneToMany::make('languages')
                                              ->setForeignKey('content_id')
                                              ->setForeignGuards(['content_type' => 'products']))
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
          ->addRelation('ebay', OneToOne::make('ebay_products'))
        // behaviours
          ->addBehaviour(Timestamps::make('created_on', 'updated_on'))
          ->addBehaviour(SoftDelete::make('deleted_on'))
);

$orm->addMapper(
    Mapper::make('ebay_products')
          ->setTable('tbl_ebay_products')
        // columns
          ->addAutoIncrementColumn()
          ->addColumn(Column::bigInteger('product_id', true)->setIndex(true))
          ->addColumn(Column::decimal('price', 14, 2)->setDefault(0))
          ->addColumn(Column::bool('is_active')->setIndex(true))
);

$orm->addMapper(
    Mapper::make('languages')
          ->setTable('tbl_languages')
        // columns
          ->addAutoIncrementColumn()
          ->addColumn(Column::varchar('content_type', 100)->setIndex(true))
          ->addColumn(Column::bigInteger('content_id', true)->setIndex(true))
          ->addColumn(Column::varchar('lang', 5)->setIndex(true))
          ->addColumn(Column::string('title'))
          ->addColumn(Column::string('description')->setNullable(true))
);

$orm->addMapper(
    Mapper::make('images')
          ->setTable('tbl_images')
        // columns
          ->addAutoIncrementColumn()
          ->addColumn(Column::varchar('imageable_type', 100)->setIndex(true))
          ->addColumn(Column::bigInteger('imageable_id', true)->setIndex(true))
          ->addColumn(Column::string('path'))
          ->addColumn(Column::json('title'))
          ->addColumn(Column::json('description')->setNullable(true))
);

$orm->addMapper(
    Mapper::make('tags')
          ->addAutoIncrementColumn()
          ->addColumn(Column::string('name')->setUnique(true))
);

$orm->addMapper(
    Mapper::make('categories')
        // columns
          ->addAutoIncrementColumn()
          ->addColumn(Column::bigInteger('parent_id', true)->setNullable(true)->setIndex(true))
          ->addColumn(Column::integer('position', true)->setDefault(0))
          ->addColumn(Column::string('name')->setUnique(true))
        // relations
          ->addRelation('parent', ManyToOne::make('categories'))
          ->addRelation('children', OneToMany::make('categories'))
          ->addRelation('languages', OneToMany::make('languages')
                                              ->setForeignKey('content_id')
                                              ->setForeignGuards(['content_type' => 'products']))
);

$generator = new ClassGenerator($orm);
$generator->writeFiles();

return $orm;

