---
title: The Casting Manager | Sirius ORM
---

# Architecture - The Casting Manager

The Sirius ORM registers for internal usa a **Casting Manager** which is charge of converting and transforming values. It acts as a singleton and is passed around from the ORM instance to mappers and even entities (see `GenericEntity` class)

The Casting Manager is a registry of functions that receive a value and return a "transformed" value:

- '1' can be cast as `int` into 1
- '1' can be cast as `bool` into TRUE
- an array can be cast into an entity
- a set of arrays can be cast into a collection of entities

The Casting Manager is populated with functions by each mapper, functions that delegate to each of the mapper's entity factory to cast arrays as entities or collections of entities

It is also used by the `GenericEntity` class to ensure the entity's attributes are properly casted.

You can use for things like creating Carbon dates for some columns:

```php
use Carbon\Carbon;

$orm->getCastingManager()
    ->register('date', function($value) {
        return Carbon::parse($value);
    });
```

Since an entity attribute must also be serialized back when persisting you need to define a "cast_for_db" function like so
```php
use Carbon\Carbon;

$orm->getCastingManager()
    ->register('date_for_db', function($value) {
        if ($value instanceof Carbon) {
            return $value->format('%Y-%m-%d');
        }
        return (string) $value;
    });
```



and if you use the `GenericEntity`-based entities

```php
use Sirius\Orm\Entity\GenericEntity;

class Page extends GenericEntity {
    protected $casts = ['published_at' => 'date'];
}




```