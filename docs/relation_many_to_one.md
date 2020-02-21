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
use Sirius\Orm\Relation\RelationConfig;

$orm->register('products', MapperConfig::make(
    // other mapper config goes here
    'relations' => [
        'category' => [
            RelationConfig::TYPE           => 'many_to_one',
            RelationConfig::FOREIGN_MAPPER => 'categories',
            RelationConfig::NATIVE_KEY     => 'category_id',            
            RelationConfig::FOREIGN_KEY    => 'id',            
            RelationConfig::QUERY_CALLBACK => function($query) {
                $query->orderBy('display_priority DESC');
                return $query;
             }
        ]       
    ]
));
``` 