---
title: Cookbook - table inheritance | Sirius ORM
---

# Cookbook - Table inheritance

Sometimes you need to use the same table to store multiple type of entities. If you are familiar with Wordpress, the `posts` table stores pages, posts and custom content types.

## Solution 1. One mapper per entity type

If you don't want the mapper to query multiple entity types you can use [guards](the_guards.md) you configure the mapper like this

```php
$pageConfig = MapperConfig::fromArray([
    MapperConfig::ENTITY_CLASS  => 'App\Entity\Content\Page',
    MapperConfig::TABLE         => 'content',
    MapperConfig::GUARDS        => ['content_type' => 'page']
]);
$orm->register('pages', $pageConfig);
```

## Solution 2. Custom entity hydrator

If you want to query the mapper for all types of content BUT you want the rows in the table to be converted into different entity types you need a custom Hydrator.

```php
use Sirius\Orm\GenericEntityHydrator;

class CustomHydrator extends GenericEntityHydrator { 
    public function hydrate($arr) {
        if ($arr['content_type'] == 'Post') {
            return new Page($arr);
        }
        return new Content($arr);
    }
}
```

```php
$customEntityHydrator = new CustomHydrator;

$contentConfig = MapperConfig::fromArray([
    MapperConfig::ENTITY_CLASS   => 'App\Entity\Content',
    MapperConfig::TABLE          => 'content',
    MapperConfig::ENTITY_FACTORY => $customEntityHydrator
]);
$orm->register('content', $contentConfig);
```

In this case the `$customEntityFactoryInstance` will be in charge of converting a row into the proper type of content

**Warning!** This solution poses some problems

1. Since relations are defined on a per-mapper basis, all the relations attached to the "content" mapper are considered to be applicable for all generated entities (pages, posts, products).
Thus, the ORM will try to attach relations where you might not want to (eg: pages don't have many "price_rules" like products may have)
2. You are in charge of setting the `content_type` attribute on the entity so persistence works properly
3. Before persisting an entity the mapper checks if it can do it by invoking `assertCanPersistEntity()`. 
If you are not using a custom mapper to overwrite this method it will check if the entity that's about to be persisted has the same class as
 the `$mapper->entityClass` property. So you would need to make sure that your entity classes have the proper inheritance structure.

For this reason we recommend using guards to create specific mappers and a general-purpose mapper that allows you to interact with the entities in a "unified" way.