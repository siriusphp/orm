---
title: Entity patching | Sirius ORM
---

# Entity patching

One of the most frequent operations in a web application is taking data from the request and changing the entity according to that data.

This is easy for an Active Record solution like Eloquent where the entity knows everything about the database structure, column casts etc. 
While **Sirius\Orm**'s entities don't have this burden there's no need to write countless lines with `$entity->setThis()` and `entity->setThat()` because you can use the mapper's `patch()` method.

A simple update operation would be done like so

```php
$product = $productMapper->find($request->route('id'));

$productMapper->patch($product, $request->post());

$productMapper->save($product, true); // save with all relations
```

But as they say, _"there are no solution, only trade-offs"_. So:

1. The `patch()` method will use all the data it gets. If you have 10 levels of relations, it will try to create 10 levels of relations. It is up to you to clean up the data and include only what you want to be updated
2. For many-to-many and one-to-many relations the patch+save combo will remove the relations that don't exist in the patching data. 
For example if a product has-many images A, B, C and the images B and D are in the post, the links to images A and C will be removed.
This happens because the library cycles through the related collection of entities and determines wheter to add, change or delete an entity. During save, all these changes are converted into SQL operations (INSERT/UPDATE/DELETE).

Next: [Entity hydrators](entity_hydrators.md)
