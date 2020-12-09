---
title: Query joins | Sirius ORM
---

# Query JOINs

You can join with mappers or with tables.

## JOINing mappers

If you want to join related mappers you have to use `joinWith`

```php
$products = $productMapper->newQuery()
    ->joinWith('images')
    ->where('images.approved', 1)
    ->get();
```

Things to consider:

1. The ORM will create a subselect that is joined with the "main" table and it is referenced AS the name of the relation, **NOT** the underlying table. Keep this in mind when choosing the columns for `where`, `orderBy` etc.
2. The ORM will not make any `groupBy` calls so you are in charge of this. If you join with a one-to-many or many-to-many relations you need to issue a `groupBy` so you don't get duplicates. 
The ORM can't make this decision for you since it doesn't know the purpose of the join

You can skip the `joinWith()` calls if call `where()` using a column from another mapper like so:

```php
$products = $productMapper->newQuery()
    ->where('images.approved', 1)
    ->get();
```

Since `applyFilters()` calls `where()` this will also work:

```php
$products = $productMapper->newQuery()
    ->applyFilters([
        'tags.name' => ['contains' => 'cool']
    ])
    ->get();
```

> **Important!** Joining with another mapper is done only once, no matter how many times it is called.

## JOINing tables

```php
$products = $productMapper->newQuery()
                ->join('INNER', 'tbl_categories as categories', 'categories.id = products.id')
                ->where('categories.id', 10)
                ->get();
```

## Aggregates and additional columns

If you do any joins you may want to include additional columns in the results using the `columns()` method. Those columns will be attached "as is" to the entity, if the mapper's hydrator knows how to handle them

```php
$products = $productMapper->newQuery()
                ->joinWith('images')
                ->where('images.approved', 1)
                ->groupBy('products.id')
                ->columns('COUNT(images.id) AS images_count')
                ->get();
```

Next: [Batch processing](query_batches.md) 
