---
title: Cookbook - Polymorphic associations | Sirius ORM
---

# Cookbook - Polymorphic associations

There are situations where you want to use a single table to store one type of entities (eg: comments) that related to multiple other types of entities (products, blog posts etc) which reside in different tables. 
In this example the `comments`  table would have to include, beside the ID of the attached entity (eg: product) an additional column (eg: `commentable_type`) that refers to the type of entity that is linked to.
This column is called a "discriminator" column.

While other ORMs use the concept of "morphs" for this type of relations we try to keep things as simple as possible so this can be achieved using regular relations and [guards](the_guards).


## Solution 1. Using guards on relations

This is how you would define the "one-to-many" relation between a product and it's comments where the `comments` table is also used by other entities

```php
use Sirius\Orm\MapperConfig;
use Sirius\Orm\Relation\RelationConfig;

$productsConfig = MapperConfig::fromArray([
    MapperConfig::ENTITY_CLASS  => Product::class,
    MapperConfig::TABLE         => 'products',
    MapperConfig::RELATIONS => [
        'comments' => [
            RelationConfig::NAME            => 'comments',
            RelationConfig::TYPE            => RelationConfig::TYPE_ONE_TO_MANY,
            RelationConfig::FOREIGN_MAPPER  => 'comments', // name of the comments mapper as registered in the ORM
            RelationConfig::FOREIGN_KEY     => 'commentable_id',
            RelationConfig::FOREIGN_GUARDS  => ['commentable_type' => 'product'], // That's it!
        ]       
    ]
]);
$this->orm->register('products', $productsConfig);
```

After this set up all queries made on the products' related comments will have the guards added and any comment related to a product that is persisted to the database will have it's `commentable_type` column automatically set to 'product'

## Solution 2. Using guards on mappers

For this solution you define a specific mapper for the "Product Comments" which contains the guards and make the products have many "product comments", like so:

```php
use Sirius\Orm\MapperConfig;
use Sirius\Orm\Relation\RelationConfig;

$productCommentsConfig = MapperConfig::fromArray([
    MapperConfig::ENTITY_CLASS  => Comment::class,
    MapperConfig::TABLE         => 'comments',
    MapperConfig::GUARDS        => ['commentable_type' => 'product']
]);

$productsConfig = MapperConfig::fromArray([
    MapperConfig::ENTITY_CLASS  => Product::class,
    MapperConfig::TABLE         => 'products',
    MapperConfig::RELATIONS     => [
        'comments' => [
            RelationConfig::NAME            => 'comments',
            RelationConfig::TYPE            => RelationConfig::TYPE_ONE_TO_MANY,
            RelationConfig::FOREIGN_MAPPER  => 'product_comments', 
            RelationConfig::FOREIGN_KEY     => 'commentable_id'
        ]       
    ]
]);
$this->orm->register('product_comments', $productCommentsConfig);
$this->orm->register('products', $productsConfig);
```

This solution relies on the mapper to ensure the proper value is set on the `commentable_type` column at the time it is persisted.