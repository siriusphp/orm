---
title: Mapper Events | Sirius ORM
---

# Mapper events

If you inject a PSR-14 compatible event dispatcher in your ORM instance you will be able to respond to such events. The events are prefixed with the name of the mapper as registered in the ORM and a dot (ex: `products.saving`)

The events are classes from the `Sirius\Orm\Event` namespace.

This is the list of available events and their corresponding event classes:

- **query** - triggered when a query is constructed. Event class `NewQuery`
- **entity** - triggered when an entity is constructed. Event class `NewEntity`
- **saving** - triggered before an entity is saved. Event class `SavingEntity`
- **saved** - triggered after an entity is saved. Event class `SavedEntity`
- **deleting** - triggered before an entity is deleted. Event class `DeletingEntity`
- **deleted** - triggered after an entity is deleted. Event class `DeletedEntity`
