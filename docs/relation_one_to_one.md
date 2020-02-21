---
title: One to one relations | Sirius ORM
---

# "One to one" relations

Also known as "__has one__" relation in other ORMs. 

On the surface, this relation seems similar to the "many to one". For example you could think that one page has one parent but you would be wrong to classify it as a "one to one" relation.

The difference is given by the order of operations when saving the table rows. In the "one page has one parent page" scenario you would save first the parent parent page (so you get the ID) and then the child page. This is why it is actually a "many pages have one parent page".
 
In a "one to one" relation you first save the "main" entity and then the related entity. One example would be to have a general "content" table and other special tables for each type of content (eg: "content_products", "content_pages") that stores
 fields specific to each table.  
In this scenario you first save row in the "content" table. Sure, you could have many rows in the "content_products" table for each row in the "content" table (which would make it a "many content_products have one content") but the ORM will only
 return the first.

There are no special options for this type of relation, besides those explained in the [relations page](relations.html). 

In this scenario you would probably want to do CASCADE delete but for this relation the default is still FALSE because usually this happens directly in the database.


## Definining a one-to-one relation

Here's a typical example

```php
use Sirius\Orm\Relation\RelationConfig;

$orm->register('products', MapperConfig::make(
    // other mapper config goes here
    'relations' => [
        'fields' => [
            RelationConfig::TYPE           => 'one_to_one',
            RelationConfig::FOREIGN_MAPPER => 'product_fields',
            RelationConfig::NATIVE_KEY     => 'id',
            RelationConfig::FOREIGN_KEY    => 'product_id',
            // most likely you want to cascade deletes if the DB doesn't
            RelationConfig::CASCADE        => true,
            // most likely you would want the fields from the start
            RelationConfig::LOAD_STRATEGY  => RelationConfig::LOAD_EAGER
        ]       
    ]
));
```  