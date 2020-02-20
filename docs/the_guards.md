---
title: Entity guards | Sirius ORM
---

# Architecture - Entity Guards

There are many situations where you want to restrict the behaviour of the query used to select entities based on columns that have a specific value AND you also want to enforce those values at the moment the entities are persisted.

Guards are key-value pairs that correspond to columns that are used while querying or persisting entities and they can be used on Mappers and Relations.

Example: guard to force the `content_type` column to be equal to `page`

```php
use Sirius\Orm\MapperConfig;

$pageConfig = MapperConfig::fromArray([
    MapperConfig::ENTITY_CLASS => Page::class,
    MapperConfig::TABLE => 'content',
    MapperConfig::GUARDS => ['content_type' => 'page'] //---- HERE
]);

$orm->register('pages', $pageConfig);
```

This will make all sure that

1. the SELECT queries include a `AND content_type="page"` condition
2. the INSERT\UPDATE quries will include a `SET content_type="page"` instruction