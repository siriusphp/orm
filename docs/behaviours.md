---
title: Behaviours | Sirius ORM
---

# Behaviours

Behaviours are objects that alter the... behaviours of mappers.

The are attached to the mapper's definition:

```php
$orm->register('products', MapperConfig::fromArray([
    // other mapper config goes here
    'behaviours' => [new SoftDelete('deleted_at')]
]));
```

or on the fly:

```php
$orm->get('products')->use(new SoftDelete('deleted_at'));
```

You can disable temporarily one behaviour like this:

```php
$orm->get('products')->without('soft_delete')
    ->newQuery();
```

This would clone the mapper and let you work with it under the new configuration. However the registered mapper instance will still be there on the next `$orm->get('products')`

The Sirius ORM comes with 2 behaviours:

> #### Soft Delete

> ```php
> $orm->get('products')
>     ->use(new SoftDelete('name of the column with the date of delete'));
> ```

> #### Timestamps

> ```php
> $orm->get('products')
>     ->use(new Timestamps('column for create', 'column for update'));
> ```

## Temporarily disable behaviours

Behaviours should be active all the times but sometimes you need to disable them for a short period of time. In the case of Soft Deletes you may want to query entities that were deleted. If you want o restore an entity you can do the following:

```php
$allProductsMapper = $orm->get('products')->without('soft_deletes');
$deletedProduct = $allProductsMapper->find($id);
$deletedProduct->deleted_at = null;
$allProductMapper->save($deletedProduct);
```

## Create your own behaviours

Behaviours can intercept the result of various methods in the mapper and can alter that result or provide a new one.

For example, the `SoftDelete` behaviour intercepts the `Delete` action that is generated when you call `$mapper->delete($entity)` and returns another action of class `SoftDelete` which performs an update. It also intercepts new queries and sets a
 guard `['deleted_at' => null]`.
 
 The mechanism by which this happens is this: when a mapper's method is called and it is allowed to be intercepted by behaviours the mappers will try to call the `on[method]` methods on the attached behaviours. 
 
 The `SoftDelete` behaviours implements `onDelete()` (for the mapper's `delete` method) and `onNewQuery` (for the mapper's `newQuery` method).
 
 Below is the list of methods that can be intercepted by behaviours:
 
 - `newEntity` - used to create a new entity (from an array or a table rows)
 - `extractFromEntity` - used to extract the columns that have to be persisted
 - `save`
 - `delete`
 - `newQuery` 

Check out the `SoftDelete` and `Timestamps` classes for inspiration