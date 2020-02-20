---
title: One to many relations | Sirius ORM
---

# "One to many" relations

Also known as "__has many__" relation in other ORM. 

Examples: 
- one product has many images, 
- one category has many products.

There are no special options for this type of relation, besides those explained in the [relations page](relations.html).

Most of the times (like in the examples above) you don't want to CASCADE delete so this defaults to FALSE. 
One use-case where you want to enable this behaviours is on "one order has many order lines" where you don't need the order lines once the
 order is deleted.
 But then again, you should let the DB do this.
 
## Defining a one-to-many relation

In this case the `media` table holds other type of files, not just images

```php
$orm->register('products', MapperConfig::make(
    // other mapper config goes here
    'relations' => [
        'images' => [
            'type'           => 'many_to_one',
            'foreign_mapper' => 'media',
            'foreign_key'    => 'p_id', 
            'foreign_guards' => ['media_type' => 'image'],           
            'query_callback' => function($query) {
                $query->orderBy('display_priority DESC');
                return $query;
             }
        ]       
    ]
));
```  