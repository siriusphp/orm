---
title: Searching mappers | Sirius ORM
---

# Searching for entities

Searching for entities is pretty simple. If you have experience with other query builders you should be comfortable writting queries right away

Under the hood Sirius ORM uses [Sirius\Sql](https://www.sirius.ro/php/sirius/sql/) and all its querying are also available in the ORM. 

**Important!** If you decide to use the `fetch` and `yield` methods keep in mind they won't return entities but table rows.
Only the `find`, `first`, `get` and `paginate` will process the rows through the `EntityHydrator` object. 
See [direct queries](direct_queries.md) for further details. 

##### Finding entities by primary key

```php
$orm->find('products', 1);
// or via the mapper
$orm->get('products')->find(1);
```

This returns a single entity or NULL.

##### Searching entities

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


##### Paginating entities

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
            'images' => function($query) {
                $query->orderBy('priority');
                return $query;
            }
        ]
    );
```


**Warning!** Using `limit` and `offset` in the query callback does not work as you might expect when you want more than 1 entity back (ie: when you are not using `find` or `first`). 

```php
$orm->get('products')
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

The above code will not return 2 images per product but 2 images for the entire query. If the query returns 10 products only 2 images will be found by the subsequent query.


If you want to use eager-loading with `find` you need to specify it as the second argument

```php
$orm->get('products')
    ->find(1, [
        'category',
        'category.parent',
        'images' => function($query) {
            $query->orderBy('priority');
            return $query;
        }
    ]);
```

**Note!** Using `limit` and `offset` on queries of type `find` or `first` will work as expected.

## JOINing mappers

If you want to join related mappers you have to use `joinWith`

```php
$orm->select('products')
    ->joinWith('images')
    ->where('images.approved', 1)
    ->get();
```

Things to consider:

1. The ORM will create a subselect that is JOINed with the "main" table and it is referenced AS the name of the relation, NOT the underlying table. Keep this in mind when you do `where`, `orderBy` etc.
2. The ORM will not make any `groupBy` calls so you are in charge of this. If you join with a one-to-many or many-to-many relations you need to issue a `groupBy` so you don't get duplicates. The ORM can't make this decision for you since it doesn
't know the purpose of the join

## JOINing tables

Although not recommended you can still issue joins with other tables. 
 
```php
$products = $orm->select('products')
                ->join('INNER', 'tbl_categories as categories', 'categories.id = products.id')
                ->where('categories.id', 10)
                ->get();
```

## Aggregates and aditional columns

If you do any joins you may want to include additional columns in the results using the `columns()` method. Those columns will be attaches "as is" to the entity, if the mapper's `EntityHydrator` allows it

```php
$products = $orm->select('products')
                ->joinWith('images')
                ->where('images.approved', 1)
                ->groupBy('products.id')
                ->columns('COUNT(images.id) AS images_count')                        
                ->get();
```

## Batch-processing entities

Entities can consume lots of memory so, when you need to process a lot of them, you may need to batch-process them.

For this you have to use the `chunk()` method of the query

```php
$orm->select('products')
    ->chunk(100, function($product) {
        // do something with the product 
        // this callback is for a single entity, not the chunk itself
    });
```

There are times when you want to process a large numbers of entities but you know that, based on your query, there shouldn't be that many chunks to be processed. If that's the case, you can set a limit on the number of chunks to be processed.

 ```php
 $orm->select('products')
     ->chunk(100, function($product) {
         // do something with the product 
     }, 100 /** no more than 100 chunks */);
 ```
