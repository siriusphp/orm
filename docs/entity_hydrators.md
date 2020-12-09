---
title: Entity hydrators | Sirius ORM
---

# Entity hydrators

The entity hydrators are objects responsible for:

1. Hydrating query results into entities
2. Extracting data from entities to be used by actions for INSERT/UPDATE/DELETE queries.
3. Setting properties on existing entities

These objects are constructed in the mapper and there must be one hydrator per mapper since the hydrator is also aware of the mapper.

The hydrator is also used by relations to inject [lazy value loaders](the_lazy_values.md) and attach related entities to another entity.

**Sirius\Orm** comes with 2 hydrators for each style of entity (properties/methods).

> You can create your own hydrators as long as they implement the proper contract but for the moment there is no support for custom hydrators while defining the mappers.
> This means you will have to implemet this in your 'xxxMapper' classes like so

```php
class ProductMapper extends ProductMapperBase
{
    protected function init() {
        parent::init();
        $this->entityHydrator = new CustomHydrator($this->orm->getCastingManager());
        $this->entityHydrator->setMapper($this);
    }
}
```

Next: [Relations](relations.md)
