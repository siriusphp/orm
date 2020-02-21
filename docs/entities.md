---
title: Working with entities | Sirius ORM
---

# Working with entities

## Creating new entities

You can ask the mapper to generate new entities for you using the hydrator. 
This will free you from having to build them yourself as it will also construct the proper relations

```php
$product = $orm->get('products')
    ->newEntity([
        'name' => 'iPhone XXS',
        'sku'  => 'ixxs',
        'price'=> 1000.50,
        'category' => [
            'name' => 'Smart phones',
        ],
        // and on and on...
    ]);
```

The above code will construct a Category entity and associate it with the Product entity and so on and so forth.

## Manipulating the entities

Once you have an entity from the mapper (from `newEntity` or from a querry) you can manipulate it as described by its interface. In the case of the `GenericEntity` class you can perform things like

```php
$product = $orm->find('products', 1);
$product->category->name = 'New category name'; // this works with lazy loading
$product->images->get(0)->path = 'new_image.jpg'; // this too
```

One-to-many and Many-to-many relations return Collections, which extend the Doctrine's [ArrayCollection](https://www.doctrine-project.org/projects/doctrine-collections/en/1.6/index.html) so you can do things like

```php
if (!$product->images->isEmpty()) {
    $product->images->first();
}

$paths = $product->images->map(function($image) {
    return $image->path;
});
```

## Persisting the entities

```php
$orm->get('products')->save($product);
```

It's that simple! The actions required for persisting an entity are wrapped in a transaction and the ORM will search for all the changes in the "entity tree" and perform the necessary persisting action. You can learn more about the
 persistence actions [here](the_actions.md)
 
If you don't want for the ORM to look up the "entity tree" and you want to persist only the "root entity" you can do this

```php
$orm->get('products')->save($product, false); // it will only persist the product row
```

If you want to persist only specific parts of the "entity tree" you do this:

```php
$orm->get('products')->save($product, ['category', 'category.parent', 'images']);
```

## Deleting entities

```php
$orm->get('products')->delete($product);
```