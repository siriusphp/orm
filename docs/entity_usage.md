---
title: Working with entities | Sirius ORM
---

# Working with entities

## Creating new entities

You can ask the mapper to generate new entities for you using the hydrator. 
This will free you from having to build them yourself as it will also construct the proper relations

```php
$product = $productMapper->newQuery()
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

Once you have an entity from the mapper (from `newEntity` or from a querry) you can manipulate it as described by its interface. 

In the case of the `GenericEntity` class you can perform things like

```php
$product = $productMapper->find(1);
$product->category->name = 'New category name'; // this works with lazy loading
$product->images->get(0)->path = 'new_image.jpg'; // this too
```

In the case of the `ClassMethodsEntity` it would work like so
```php
$product = $productMapper->find(1);
$product->getCategory()->setName();
$product->getImages()->get(0)->setPath('new_image.jpg');
```


One-to-many and Many-to-many relations return Collections, which extend the Doctrine's [ArrayCollection](https://www.doctrine-project.org/projects/doctrine-collections/en/1.6/index.html) so you can do things like

```php
if (!$product->getImages()->isEmpty()) {
    $product->getImages()->first();
}

$paths = $product->getImages()->map(function($image) {
    return $image->path;
});
```

## Persisting the entities

You can learn about persisting the entities on the [mapper usage](mapper_usage.md) section

Next: [Entity patching](entity_patching.md)
