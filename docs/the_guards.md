---
title: Entity guards | Sirius ORM
---

# Architecture - Entity/Query Guards

There are many situations where you want to restrict the behaviour of the query used for selecting entities based on columns that have a specific value AND you also want to enforce those values at the moment the entities are persisted.

Guards are key-value pairs that correspond to columns that are used while querying or persisting entities and they can be used on `Mappers`, `Relations` and `Behaviours`.

Example: guard to force the `content_type` column to be equal to `products`

```php
$orm->addMapper(
    Mapper::make('product_languages')
          ->setTable('tbl_languages')
          ->setGuards(['content_type' => 'products'])
        // columns
          ->addAutoIncrementColumn()
          ->addColumn(Column::varchar('content_type', 100)->setIndex(true))
          ->addColumn(Column::bigInteger('content_id', true)->setIndex(true))
          ->addColumn(Column::varchar('lang', 5)->setIndex(true))
          ->addColumn(Column::string('title'))
          ->addColumn(Column::string('slug'))
          ->addColumn(Column::string('description')->setNullable(true))
);
```

The scenario above allows you to store multiple content types in the same table.

This will make all sure that

1. the SELECT queries include a `AND content_type="page"` condition
2. the INSERT\UPDATE quries will include a `SET content_type="page"` instruction



The guards are available for:

- **mappers**, to ensure only specific entities are queried AND that persisted queries have specific values for some columns. See example above.
- **relation**, to ensure additional filters being applied when querying for related entities and persisting relation. 
For example you could have a `tags` mapper that holds all the tags and and a `links_to_tags` table that holds the relations between all tags and various types of content in the app. 
Tag with ID 1 could be linked to the page with ID 1 and to the product with ID 1. Adding a `content_type` discriminator column to the `links_to_tags` table would allow for this scenario.
But for the mappers to work, you will have to specify the guards for each relation. 
See more about this on the [Relations section](relations.md).
