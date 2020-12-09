---
title: Mapper usage | Sirius ORM
---

# Using mappers

Mappers are used for generating new entities, retrieving entities from queries via [queries](queries.md) and persisting entity changes.

###1.Generating new entities

```php
// the $productMapper was previously retrieve somehow
// and $someArray can be values received via $_POST
$product = $productMapper->newEntity($someArray);
```
one can also generate a collection of entities directly
```php
$products = $productMapper->newCollection($someArrayOfArrays);
```

> **Important!** The entities that are generated will use all the data provided and will also create the related entities. If `$someArray` contains a `tags` element it will be used to create `Tag` entities. 
It's up to you provide an array that contains only the data required to regenerate the desired result.

###2. Querying for entities

```php
// find by primary key (usually `id`)
$product = $productMapper->find(10);

// complex query
$products = $productMapper->newQuery()
        ->where('price', 10, '>')
        ->orderBy('name')
        ->get();
```  

You can learn more about the querying capabilities in the [queries](queries.md) section.

###3. Persisting entities

##### Saving entities

```php
// save the product only
$productMapper->save($product);

// save the product and all related entities without a depth limit
$productMapper->save($product, true);

// save the product and some related entities
$productMapper->save($product, ['category', 'category.parent', 'tags']);
``` 

##### Deleting entities

```php
// delete the product only
$productMapper->delete($product);

// delete the product and all related `cascaded`!!! entities
$productMapper->delete($product, true);

// delete the product and some related entities
$productMapper->save($product, ['images', 'price_rules']);
``` 

It's that simple! The actions required for persisting an entity are wrapped in a transaction and the ORM will search for all the changes in the "entity tree" and perform the necessary persisting action. You can learn more about the
 persistence actions [here](the_actions.md)


Next: [Mapper behaviours](mapper_behaviours.md) 
