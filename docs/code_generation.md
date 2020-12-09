---
title: Code generation | Sirius ORM
---

# Code generation

As mentioned in the introduction, **Sirius ORM** relies on code generation to allow for code completion support, great peformance and flexibility. 

Code generation is a 2 step process: 
1. defining the mappers (or creating the blue prints). It uses classes  in the `Sirius\Orm\Blueprint` namespace. [Sample definition file](https://github.com/siriusphp/orm/blob/master/tests/resources/definitions.php)
2. creating the actual files on disk. It uses classes in the `Sirius\Orm\CodeGenerator` namespace. [Sample generated files](https://github.com/siriusphp/orm/tree/master/tests/Generated)

For each type of entity/mapper, the ORM 6 generates 6 classes (an approach inspired by [Propel](http://propelorm.org/)). 

In the case of a `Product` mapper the following files will be generated:
- `ProductMapperBase` - stores the configuration of the mapper and its basic methods (find, newEntity, newCollection etc). This class is over-written whenever the code generation script is called. 
- `ProductMapper` - this only extends the base mapper class and it is at your disposal to extend it according to your custom needs. This class is generated **only** if the file is not already present on disk. 
- `ProductQueryBase` - stores the query class that is specific to this mapper. It contains specific methods required by the mapper's definition. This class is over-written whenever the code generation script is called. 
- `ProductQuery` - again, this extends the query base class and it is at your disposal to augment it according to your needs. This class is generated **only** if the file is not already present on disk.
- `ProductBase` - stores the basic functionality of the entity handled by the mapper. This class is over-written whenever the code generation script is called. 
- `Product` - extends the base entity class and you can extend it as you see fit. Again, not over-written during code generation.

> The base classes are abstract and cannot be injected as dependencies. 

##1. Defining the mappers

You will learn about various aspects of the available options for code generation under the next chapters (eg: mappers, entities etc) and you can see a working example in the repo's 
[test folder](https://github.com/siriusphp/orm/blob/master/tests/resources/definitions.php)

To give a taste of what is needed to get up-and-running with **Sirius ORM** here's a short snipped:

```php
require_once __DIR__ . '/../../vendor/autoload.php';

use Sirius\Orm\Blueprint\Behaviour\Timestamps;
use Sirius\Orm\Blueprint\Column;
use Sirius\Orm\Blueprint\ComputedProperty;
use Sirius\Orm\Blueprint\Mapper;
use Sirius\Orm\Blueprint\Orm;
use Sirius\Orm\Blueprint\Relation\ManyToMany;
use Sirius\Orm\Blueprint\Relation\ManyToOne;
use Sirius\Orm\Blueprint\Relation\OneToMany;
use Sirius\Orm\Blueprint\Relation\OneToOne;
use Sirius\Orm\Query;
use Sirius\Orm\Relation\RelationConfig;

/**
 * register the defaults for the mapper definitions that comes next
 * - namespaces  
 * - folder destinations  
 */
$orm = Orm::make()
          ->setMapperNamespace('Sirius\\Orm\\Tests\\Generated\\Mapper')
          ->setMapperDestination(__DIR__ . '/../Generated/Mapper/')
          ->setEntityNamespace('Sirius\\Orm\\Tests\\Generated\\Entity')
          ->setEntityDestination(__DIR__ . '/../Generated/Entity/');

/**
 * register a mapper's definition
 * - columns, attributes and computed properties
 * - relations and aggregators
 * - behaviours
 */
$orm->addMapper(
    Mapper::make('products')
          ->setTable('products')
        // columns
          ->addAutoIncrementColumn()
          ->addColumn(Column::integer('category_id', true))
          ->addColumn(Column::varchar('sku')->setUnique(true))
          ->addColumn(Column::decimal('price', 14, 2)
                            ->setAttributeName('value')
                            ->setDefault(0))
          ->addColumn(Column::json('attributes'))
        // computed property
          ->addComputedProperty(ComputedProperty::make('discounted_price')
                                                ->setType('float')
                                                ->setGetterBody('return round($this->value * 0.9, 2);')
                                                ->setSetterBody('$this->value = $value / 0.9;'))
        // relations
          ->addRelation('languages', OneToMany::make('product_languages')
                                              ->setForeignKey('content_id'))
          ->addRelation('images', OneToMany::make('images')
                                           ->setForeignKey('content_id')
          ->addRelation('tags', ManyToMany::make('tags')
                                          ->setPivotTable('products_to_tags')
                                          ->setQueryCallback(function (Query $query) {
                                              $query->orderBy('position ASC');

                                              return $query;
                                          })
                                          ->addAggregate('tags_count', [RelationConfig::AGG_FUNCTION => 'count(tags.id)']))
          ->addRelation('category', ManyToOne::make('categories'))
          ->addRelation('ebay', OneToOne::make('ebay_products'))
        // behaviours
          ->addBehaviour(Timestamps::make('created_on', 'updated_on'))
);
```   

The classes in the `Sirius\Orm\Blueprint` namespace have fluent interfaces, so you don't have to remember all the options available.

> **Important!** The `Blueprint` behaviours are not the same as the mapper behaviours (to be discussed later) and they are used to modify (add properties, methods and traits) the base classes that are being generated.

## 2. Generating the files

It's as simple as 

```php
$generator = new \Sirius\Orm\CodeGenerator\ClassGenerator($orm);
$generator->writeFiles();
```

> There are some tests done prior to writting the files and, if the definition has errors, an exception will be thrown.

Next: [Mappers](mappers.md)
