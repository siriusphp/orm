---
title: Many to one relations | Sirius ORM
---

# "Many to one" relations

Also known as "__belongs to__" relation in other ORMs. 

Examples: 
- multiple images belong to one product, 
- multiple products belong to a category 
- multiple pages belong to one parent.

There are no special options for this type of relation, besides those explained in the [relations page](relations.html).

Most of the times (like in the examples above) you don't want to CASCADE delete so this defaults to FALSE.

## Definining a many-to-one relation

```php
$orm->register('products', MapperConfig::make(
    // other mapper config goes here
    'relations' => [
        'category' => [
            'type'           => 'many_to_one',
            'foreign_mapper' => 'categories',
            'native_key'     => 'cat_id',            
            'query_callback' => function($query) {
                $query->orderBy('display_priority DESC');
                return $query;
             }
        ]       
    ]
));
``` 