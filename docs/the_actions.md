# Architecture - The persistance actions

When persisting entity changes (insert/update/delete) Sirius ORM uses `Actions`. 

These are individual classes that responsible for:

1. executing the actual necessary queries
2. updating the entities after successful queries (eg: set the `id` from the `lastInsertId`)
3. revert changes made to the entities in case of exceptions

There are actions for delete, insert, update and link entities together (for many-to-many relations).

**Important!** The Sirius ORM actions are revertable. If there are exceptions while calling
a persistance method (`save` or `delete`) the entities are reverted to their
original state. This way you can use retries in your app if you face connectivity issues.

Action can also execute other actions since entities are related and, usually, when you want to persist
an entity change you want to also persist the changes to the related entities.

If you were to save a new (as in "not already in DB") product entity that has a new category 
and a set of new tags the operations required would be

1. save category entity in the database
2. set the `category_id` attribute on the product entity
3. save the product entity in the database
4. set the `product_id` attribute on each image
5. save each tag entity in the database
6. create the links between the product and tags in the pivot table

The problems Sirius ORM solves with `Action`s are:

1. All these operations are wrapped in a transaction and, at any point in time 
there could be an exception and the entity has to revert to the original state.
2. These operations have to be performed in the proper order, dictated by the
relations between the objects.

To solve these problems the Sirius ORM `Action`s are organized in a tree-like
structure that follow these rules:

1. Each action executes operations for **only one** entity 
(eg: insert the a row in the products table)
2. Each action may execute other actions before it's own operations
(eg: call the "insert new category" action)
3. Each action may execute other actions after it's own operations
(eg: call the "insert new image" action)

The relation between entities determine what type of actions have to be appended
or prepended inside each action.

DELETE actions cascade for relations that have this option set to true, otherwise a DELETE action
will also execute SAVE actions for the related entities.

Sirius ORM also gives you the option to choose how deep an action should go visavis the related entities:

```php
// go as deep as possible and save any changes made to related entities
$productsMapper->update($product, true);

// don't save anything but the product
$productsMapper->update($product, false);

// save only the products and the tags
$productsMapper->update($product, ['tags']);
```