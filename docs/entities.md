---
title: Entities | Sirius ORM
---

# Entities

We have tried to make the entities as lean as possible and and flexibile enough to match your programming style. 

Depending on your preferred style of programming you can use one of the 2 types of entities:
- properties-based entities
- methods-based entities

For both style you will have great code-editor support. For the properties-based entities we use class comments.

#### Properties-based entities

These type of entites are those where you do things like

```php
$entity->attribute;

$entity->another_attribute = $someValue;
```

#### Methods-based entities

These type of entites are those where you use getters/setters to manipulate them

```php
$entity->getAttribute();

$entity->setAnotherAttribute($someValue);
```

As you will see in the next section, you can choose what style of entity you want. You can even mix and match them, if you want.

> Theoretically you can use your own base entities if they implement the `Sirius\Orm\Contract\EntityInterface`interface but the code generation feature does not currently support it.

Entities are database-agnostic and they do not know anything about the underlying structure. For this reason the mapper use hydrators to convert data from DB to entities and back. 
For each entity style there's a matching hydrator class that is created when the mapper is initialized.

Next: [Entity definition](entity_definition.md)
