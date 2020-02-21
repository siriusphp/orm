---
title: Mappers | Sirius ORM
---

# Mappers

In _Sirius ORM_ Mappers are objects that do the following things:

- construct [queries](queries.md) for retrieving entities
- generates an executes actions for [persisting](persistence.md) entities

For this they have to know about a lot of stuff so that the entities remain database-agnostic and as "dumb" as possible: table columns, relations between table columns and entity attributes, the type of entity they handle and the relations between
 entities. 

Mappers work together and delegate operations from one to another and they are "registered" within the ORM which acts as a "mapper locator".

### 1. Registering mappers via config

Most of the times you don't need to construct a special `Mapper` class and you can use the one provided by the library. In this case you only need to register with the ORM instance the configuration options like in the example below. 

The exemple includes all config options available although you won't need them all the time (eg: table alias) and some of them have sensible defaults (eg: primary key)

```php
use Sirius\Orm\Behaviour\SoftDelete;
use Sirius\Orm\Behaviour\Timestamps;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\Relation\RelationConfig;

$orm->register('products', MapperConfig::fromArray(
    MapperConfig::ENTITY_CLASS      => 'App\Entity\Product',
    MapperConfig::TABLE             => 'tbl_products',
    MapperConfig::TABLE_ALIAS       => 'products', // if you have tables with prefixes
    MapperConfig::PRIMARY_KEY       => 'product_id', // defaults to 'id'
    MapperConfig::COLUMNS           => ['id', 'name', 'price', 'sku'],
    MapperConfig::CASTS             => ['id' => 'integer', 'price' => 'decimal:2'],
    MapperConfig::COLUMN_ATTRIBUTE_MAP => ['sku' => 'code'], // the entity works with the 'code' attribute
    MapperConfig::GUARDS            => ['published' => 1], // see "The guards" page
    MapperConfig::SCOPES            => ['sortRandom' => $callback], // see "The query scopes" page
    MapperConfig::BEHAVIOURS        => [
        new SoftDelete('deleted_at'),
        new Timestamps(null, 'updated_at')
    ],
    MapperConfig::RELATIONS         => [
        'images' => [
            RelationConfig::FOREIGN_MAPPER => 'images'
            // see the Relation section for the rest
        ]
        // rest of the relations go here       
    ] 
));
```

One advantage of this solution is that the mapper is constructed only when it is requested the first time. If your app doesn't need the `orders` mapper, it won't be constructed.

### 2. Registering mappers via factories

You can use a function when registering a custom mapper with the ORM, like so:

```php
$orm->register('products', function($orm) {
    // construct the mapper here and return it;
});
```

### 3. Register mapper instances 

```php
$productsMapper = new ProductMapper($orm);
// make adjustments here to the products mapper
// inject services, add behaviours etc

$orm->register('products', $productsMapper);
```