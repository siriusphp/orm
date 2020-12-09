---
title: Searching mappers | Sirius ORM
---

# Searching for entities

Searching for entities is pretty simple. If you have experience with other query builders you should be comfortable writting queries right away

Under the hood Sirius ORM uses [Sirius\Sql](https://www.sirius.ro/php/sirius/sql/) and all its querying capabilities are also available in the ORM. 

> **Important!** If you decide to use the `fetch` and `yield` methods keep in mind they won't return entities but table rows.
Only the `find`, `first`, `get` and `paginate` will process the rows through the `EntityHydrator` object. 
See [direct queries](queries_direct.md) for further details. 

##### Finding entities by primary key

```php
// eager load some of the relations
$productMapper->find(1, ['category', 'category.parent', 'images']);

// the above is a shortcut for
$productMapper->newQuery()->find(1, ['category', 'category.parent', 'images']);
```

This returns a single entity or NULL.

##### Searching entities

```php
$products = $productMapper->newQuery()
    ->where('price', 50, '>')
    ->get();
```
This returns a `Collection` instance which can be empty.

You can also retrieve only one entity from a select query using `first()`

```php
$product = $productMapper->newQuery()
    ->where('price', 50, '>')
    ->orderBy('price desc')
    ->first();
```

##### Paginating entities

Pagination is similar to searching except it returns a `PaginatedCollection` that also contains pagination information:
- `getTotalCount()` - total entities matching the query
- `getTotalPages()` - total number of pages
- `getCurrentPage()`
- `getPageStart()` - the index of the first row, **starts at 1**
- `getPageEnd()` - the index of the last row

```php
$paginatedProducts = $productMapper->newQuery()
    ->where('price', 50, '>')
    ->paginate(25 /* per page*/, 2 /*page number*/);
```

##### Applying filters

Most of the times you want to apply multiple conditions to filter the database. For this reason there's a covenient method called `applyFilters()`

```php
$filters = [
    'price' => ['gte' => 10, 'lte' => 20],
    'title' => ['contains', 'gold'],
    'id' => '1,2,3,4'
];
$products = $productMapper->newQuery()
    ->applyFilters($filters)
    ->get();

// same as
$products = $productMapper->newQuery()
    ->where('price', 10, '>=')
    ->where('price', 20, '<=')
    ->where('title', '%gold%', 'like')
    ->where('id', [1, 2, 3, 4], 'in')
    ->get();
```

The available operators and their aliases are:
- `<=`: 'less_or_equal', 'lte'
- `<`: 'less_than', 'lt'
- `>=`: 'greter_or_equal', 'gte'
- `>`: 'greater_than', 'gt'
- `contains`
- `starts_with`
- `ends_with`


Next: [Eager loading](query_eager_loading.md) 
