---
title: The lazy values | Sirius ORM
---

# Architecture - The Lazy Value Loaders

In order to allow for performant eager-loading (more about this on [The tracker](the_tracker.md)) the entities are injected with `LazyLoader`s.

This is the `EntityInterface` contract

```php
<?php
declare(strict_types=1);

namespace Sirius\Orm\Contract;

interface EntityInterface
{
    public function getState();

    public function setState($state);

    public function getChanges();

    public function toArray();

    public function setLazy(string $name, LazyLoader $lazyLoader);
}
```

The `setLazy()` method is used to inject values for relations and aggregates but it can be used for all the situations where you want to inject values that a) might not be needed and b) are expensive.

The implementation details of the `GenericEntity` and `ClassMethodsEntity` make it so that trying to access a property of the entity will trigger a lookup for a matching lazy loader. 
In the case of a relation when doing `$product->category` for a product that has the category relation lazy-loaded, there will be a look-up for a lazy loader registered under the name `category`. 

> **This happens even if the property is already set**

This gives room for a lot of posibilities when creating custom behaviours/actions, handling events etc.
