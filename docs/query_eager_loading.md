---
title: Eager loading | Sirius ORM
---

# Eager-loading relations

Unless specified when defining a relation, all relations are lazy loaded. Learn more on the [relations](relations.md) section. 

> **Note!** This won't affect the number of queries executed but has some cost associated with it. Check how Sirius ORM solves the (N+1) problem using [the tracker](the_tracker.md).

If you want to specifically state the relations to be loaded use the `load()` method on queries

```php
$products = $productMapper->newQuery()
    ->where('category_id', 10)
    ->load('category') // just the name of the relation
    ->load('category.parent') // go as deep as you want
    ->load([
        // use a callback to alter the relation query
        'images' => function($query) {
            $query->orderBy('priority')->limit(2);
            return $query;
        }
    ]);
```

all of the above can be written in a single line

```php
$products = $productMapper->newQuery()
    ->where('category_id', 10)
    ->load(
        'category',
        'category.parent',
        [
            // use a callback to alter the relation query
            'images' => function($query) {
                $query->orderBy('priority');
                return $query;
            }
        ]
    );
```


> **Warning!** Using `limit` and `offset` in the query callback does not work as you might expect when you want more than 1 entity back (ie: when you are not using `find` or `first`). 
The code bellow will not return 2 images per product but 2 images for the entire query. If the query returns 10 products only 2 images will be found by the subsequent query.

```php
$products = $productMapper->newQuery()
    ->where('category_id', 10)
    ->load(
        'category',
        'category.parent',
        [
            // use a callback to alter the relation query
            'images' => function($query) {
                $query->orderBy('priority')
                    ->limit(2); // <---- NOT RIGHT
                return $query;
            }
        ]
    );
```

If you want to use eager-loading with `find` you need to specify it as the second argument

```php
$product = $productMapper->newQuery()
    ->find(1, [
        'category',
        'category.parent',
        'images'
    ]);
```

Next: [JOINing](query_join.md)
