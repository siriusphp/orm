---
title: One to many relations | Sirius ORM
---

# "One to many" relations

Also known as "__has many__" relation in other ORM. 

Examples: 
- one product has many images, 
- one category has many products.

Besides the configuration options explained in the [relations page](relations.html) on "one to many" relation you can have

> ##### `aggregates` / `RelationConfig::AGGREGATES`

> - here you have a list of aggregated values that can be eager/lazy loaded to an entity (count, average, sums)
> - check the [relation aggregates](relation_aggregate.md) page for more details

Most of the times (like in the examples above) you don't want to CASCADE delete so this defaults to FALSE. 
One use-case where you want to enable this behaviours is on "one order has many order lines" where you don't need the order lines once the
 order is deleted.
 But then again, you should let the DB do this.
 
## Defining a one-to-many relation

In this case the `media` table holds other type of files, not just images

```php
use Sirius\Orm\Relation\RelationConfig;

$orm->register('products', MapperConfig::make(
    // other mapper config goes here
    'relations' => [
        'images' => [
            RelationConfig::TYPE           => 'many_to_one',
            RelationConfig::FOREIGN_MAPPER => 'media',
            RelationConfig::NATIVE_KEY     => 'id', 
            RelationConfig::FOREIGN_KEY    => 'product_id',
            // the "media" mapper holds more than images 
            RelationConfig::FOREIGN_GUARDS => [
                'media_type' => 'image'
            ],
            // order the images by a specific field
            RelationConfig::QUERY_CALLBACK => function($query) {
                $query->orderBy('display_priority DESC');
                return $query;
             }
        ]       
    ]
));
```  