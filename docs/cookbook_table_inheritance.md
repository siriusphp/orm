---
title: Cookbook - table inheritance | Sirius ORM
---

# Cookbook - Table inheritance

Sometimes you need to use the same table to store multiple type of entities. If you are familiar with Wordpress, the `posts` table stores pages, posts and custom content types.

## Solution: One mapper per entity type

If you don't want the mapper to query multiple entity types you can use [guards](the_guards.md) you configure the mapper like this

```php
$ormDefinition->addMapper(
    Mapper::make('product_languages')
          ->setTable('tbl_languages')
          ->setGuards(['content_type' => 'products']);
        // rest of the definition omitted for brevity 
```

This will ensure that:

1. The queries performed by the 'ProductLanguageMapper' will include only rows where the 'content_type' column is equal to 'products'
2. Whenever a row is inserted/updated in the 'tbl_languages' as a result of saving a 'ProductLanguage' entity, the 'content_type' column will be set to 'products'
3. Whenever a row is deleted from  the 'tbl_languages' as a result of deleting a 'ProductLanguage' entity, the 'AND content_type="products"' condition is added to the SQL query.
