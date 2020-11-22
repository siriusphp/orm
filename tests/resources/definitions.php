<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Sirius\Orm\CodeGenerator\ClassGenerator;
use Sirius\Orm\Definition\Behaviour\SoftDelete;
use Sirius\Orm\Definition\Behaviour\Timestamps;
use Sirius\Orm\Definition\Column;
use Sirius\Orm\Definition\ComputedProperty;
use Sirius\Orm\Definition\Mapper;
use Sirius\Orm\Definition\Orm;
use Sirius\Orm\Definition\Relation\ManyToMany;
use Sirius\Orm\Definition\Relation\ManyToOne;
use Sirius\Orm\Definition\Relation\OneToMany;
use Sirius\Orm\Definition\Relation\OneToOne;
use Sirius\Orm\Query;
use Sirius\Orm\Relation\RelationConfig;

$orm = Orm::make()
          ->setMapperNamespace('Sirius\\Orm\\Tests\\Generated\\Mapper')
          ->setMapperDestination(__DIR__ . '/../Generated/Mapper/')
          ->setEntityNamespace('Sirius\\Orm\\Tests\\Generated\\Entity')
          ->setEntityDestination(__DIR__ . '/../Generated/Entity/');

$orm->addMapper(
    Mapper::make('languages')
          ->setTable('tbl_languages')
        // columns
          ->addAutoIncrementColumn()
          ->addColumn(Column::varchar('content_type', 100)->setIndex(true))
          ->addColumn(Column::bigInteger('content_id', true)->setIndex(true))
          ->addColumn(Column::varchar('lang', 5)->setIndex(true))
          ->addColumn(Column::string('title'))
          ->addColumn(Column::string('slug'))
          ->addColumn(Column::string('description')->setNullable(true))
);

$orm->addMapper(
    Mapper::make('products')
          ->setTable('tbl_products')
          ->setTableAlias('products')
        // columns
          ->addAutoIncrementColumn()
          ->addColumn(Column::varchar('sku')->setUnique(true))
          ->addColumn(Column::decimal('price', 14, 2)
                            ->setAttributeName('value')
                            ->setDefault(0)
                            ->setPreviousName('cost')) // @testing: migration column rename
          ->addColumn(Column::json('attributes'))
        // computed property
          ->addComputedProperty(ComputedProperty::make('discounted_price')
                                                ->setType('float')
                                                ->setGetterBody('return round($this->price * 0.9, 2);')
                                                ->setSetterBody('$this->price = $value / 0.9;'))
        // relations
          ->addRelation('languages', OneToMany::make('product_languages')
                                              ->setForeignKey('content_id'))
          ->addRelation('images', OneToMany::make('images')
                                           ->setCascade(true)
                                           ->setForeignKey('content_id')
                                           ->setForeignGuards(['content_type' => 'products'])) // @testing: one to many | relation guards
          ->addRelation('cascade_tags', ManyToMany::make('tags')// @testing: many to many
                                                  ->setThroughTable('tbl_links_to_tags')
                                                  ->setThroughTableAlias('products_to_tags')
                                                  ->setThroughNativeColumn('tagable_id')
                                                  ->setThroughGuards(['tagable_type' => 'products'])
                                                  ->setThroughColumns(['position' => 'position_in_product'])
                                                  ->setCascade(true))
          ->addRelation('tags', ManyToMany::make('tags')// @testing: many to many
                                          ->setThroughTable('tbl_links_to_tags')
                                          ->setThroughTableAlias('products_to_tags')
                                          ->setThroughNativeColumn('tagable_id')
                                          ->setThroughGuards(['tagable_type' => 'products'])
                                          ->setThroughColumns(['position' => 'position_in_product'])
                                          ->setQueryCallback(function (Query $query) {
                                              $query->orderBy('position ASC');

                                              return $query;
                                          })
                                          ->addAggregate('tags_count', [RelationConfig::AGG_FUNCTION => 'count(tags.id)']))
          ->addRelation('cascade_category', ManyToOne::make('categories')
                                                     ->setCascade(true)) // @testing: many to one
          ->addRelation('category', ManyToOne::make('categories')) // @testing: many to one
          ->addRelation('ebay', OneToOne::make('ebay_products'))// @testing: one to one
        // behaviours
          ->addBehaviour(Timestamps::make('created_on', 'updated_on'))
          ->addBehaviour(SoftDelete::make('deleted_on'))
);

$orm->addMapper(
    Mapper::make('ebay_products')
          ->setTable('tbl_ebay_products')
          ->setEntityStyle(Mapper::ENTITY_STYLE_METHODS)
        // columns
          ->addAutoIncrementColumn()
          ->addColumn(Column::bigInteger('product_id', true)->setIndex(true))
          ->addColumn(Column::decimal('price', 14, 2)->setDefault(0))
          ->addColumn(Column::bool('is_active')->setIndex(true))
);

$orm->addMapper(
    Mapper::make('product_languages')
          ->setTable('tbl_languages')
          ->setGuards(['content_type' => 'products']) // @testing: mapper guards
        // columns
          ->addAutoIncrementColumn()
          ->addColumn(Column::varchar('content_type', 100)->setIndex(true))
          ->addColumn(Column::bigInteger('content_id', true)->setIndex(true))
          ->addColumn(Column::varchar('lang', 5)->setIndex(true))
          ->addColumn(Column::string('title'))
          ->addColumn(Column::string('slug'))
          ->addColumn(Column::string('description')->setNullable(true))
);

$orm->addMapper(
    Mapper::make('images')
          ->setTable('images')
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
          ->addRelation('parent', ManyToOne::make('categories')) // @testing: many to one
          ->addRelation('children', OneToMany::make('categories')
                                             ->setForeignKey('parent_id')
                                             ->setCascade(true))  // @testing: one to many
          ->addRelation('languages', OneToMany::make('languages')  // @testing: one to many | relation guards
                                              ->setForeignKey('content_id')
                                              ->setForeignGuards(['content_type' => 'categories'])
                                              ->setCascade(true))
          ->addRelation('products', OneToMany::make('products')
                                             ->addAggregate('lowest_price', [RelationConfig::AGG_FUNCTION => 'min(products.price)'])
                                             ->addAggregate('highest_price', [RelationConfig::AGG_FUNCTION => 'max(products.price)']))
);

$generator = new ClassGenerator($orm);
$generator->writeFiles();

return $orm;

