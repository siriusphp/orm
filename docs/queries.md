---
title: Searching mappers | Sirius ORM
---

# Searching for entities

Searching for entities is pretty simple. If you have experience with other query builders you should be comfortable writting queries right away

Under the hood Sirius ORM uses [Sirius\Sql](https://www.sirius.ro/php/sirius/sql/) and all querying capabilities there are also available in the ORM. 

**Important!** If you decide to use the `fetch` and `yield` methods keep in mind they won't return entities but table rows.
Only the `find`, `first`, `get` and `paginate` will process the rows through the `EntityHydrator` object. 
See [direct queries](direct_queries.md) for further details. 

## Finding entities by primary key

```php
$orm->find('products', 1);
// or via the mapper
$orm->get('products')->find(1);
```

This returns a single entity or NULL.

## Searching entities

```php
$orm->get('products')
    ->where('price', 50, '>')
    ->orderBy('price desc')
    ->limit(10)
    ->get();
```
This returns a `Collection` instance which can be empty.

You can also retrieve only one entity from a select query using `first()`

```php
$orm->get('products')
    ->where('price', 50, '>')
    ->orderBy('price desc')
    ->first();
```


## Paginating entities

Pagination is similar to searching except it returns a `PaginatedCollection` that also contains pagination information (total entities matching the query, current page, total pages, items per page)

```php
$orm->get('products')
    ->where('price', 50, '>')
    ->orderBy('price desc')
    ->paginate(25 /* per page*/, 2 /*page number*/);
```

## Eager-loading relations

Unless specified when registering a mapper, all relations are lazy loaded. 

**Note!** This won't affect the number of queries executed but has some cost associated with it. Check how Sirius ORM solves the (N+1) problem using [the tracker](the_tracker.md).

If you want to specifically state the relations to be loaded use the `load()` method on queries

```php
$orm->get('products')
    ->where('category_id', 10)
    ->load('category') // just the name of the relation
    ->load('category.parent') // go as deep as you want
    ->load([
        // use a callback to alter the relation query
        // in this case we only want the first 2 images
        'images' => function($query) {
            $query->orderBy('priority')->limit(2);
            return $query;
        }
    ]);
```

all of the above can be written in a single line

```php
$orm->get('products')
    ->where('category_id', 10)
    ->load(
        'category',
        'category.parent',
        [
            // use a callback to alter the relation query
            // in this case we only want the first 2 images
            'images' => function($query) {
                $query->orderBy('priority')->limit(2);
                return $query;
            }
        ]
    );
```

If you want to use eager-loading with `find` you need to specify it as the second argument

```php
$orm->get('products')
    ->find(1, [
        'category',
        'category.parent',
        'images' => function($query) {
            $query->orderBy('priority')->limit(2);
            return $query;
        }
    ]);
```

## JOINing tables

If you want to query entities and use JOINs you have to specify the table names  as they are. At the moment, Sirius ORM convert joined mappers to tables in the queries. If your category table is called `tbl_categories` you have to specify it like
 it is
 
```php
$products = $orm->select('products')
                ->join('tbl_categories as categories', 'categories.id = products.id')
                ->where('categories.id', 10)
                ->get();
```