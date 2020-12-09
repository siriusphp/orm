---
title: Mapper Behaviours | Sirius ORM
---

# Mapper behaviours

Behaviours are objects that alter the... behaviours of mappers.

The result that is produced while calling some of the mapper's methods are passed through the behaviours objects which can choose to alter them.

One such behaviour is the `Timestamps` behaviour that catches the entity that is being saved and the insert/update actions that are created and augments them by changing entity attributes and adding column-value pairs to be included in the query. 

Behaviours are initialized in the `init()` method. Here's the exemple from the generated `ProductMapperBase` in the repo's test folder

```php
abstract class ProductMapperBase extends Mapper
{
    protected $createdAtColumn = 'created_on';
    protected $updatedAtColumn = 'updated_on';
    protected $deletedAtColumn = 'deleted_on';

    protected function init()
    {
        // mapper config ommited for brevity
        $this->initRelations();
        $this->behaviours->add(new Timestamps(
                $this->createdAtColumn, 
                $this->updatedAtColumn
        ));
    }
}
```

Behaviours can be added dynamically at runtime like so

```php
$mapperWithNewBehaviour = $mapper->use(new CustomBehaviour());
```

or removed at runtime

```php
$mapperWithoutTimestamps = $mapper->without('timestamps');
```

These 2 operations would clone the mapper and let you work with it under the new configuration. However the initial registered mapper instance will still be there as it was during initialization.

The Sirius ORM comes with 2 behaviours:

##### Events

This behaviour is automatically attached to the mapper by the ORM, if the ORM has an event dispatche associated with it. 
```php
$mapper->use(new Events($eventDispatcher, 'event_prefix'));
```
 

##### Timestamps

```php
$mapper->use(new Timestamps('created_at column', 'updated_at column'));
```
 
Of course the same results could be achieved without using behaviours but we think it's a nice way to allow extending the mappers using reusable components.

## Create your own behaviours

Behaviours can intercept the result of various methods in the mapper and can alter that result or provide a new one.

For example, a `SoftDelete` behaviour could intercept the `Delete` action that is generated when you call `$mapper->delete($entity)` and return instead another action of class `SoftDelete` which performs an update. 

It could also intercepts new queries and sets a query guard `['deleted_at' => null]`.
 
 The mechanism by which this happens is this: when a mapper's method is called and it is allowed to be intercepted by behaviours the mappers will try to call the `on[method]` methods on the attached behaviours. 
 
Below is the list of "hooks" that can be intercepted and the methods that the behaviours has to implement to intercept them:
 
 - `newEntity` => `onNewEntity`
 - `newSaveAction` => `onNewSaveAction`
 - `newDeleteAction` => `onNewDeleteAction`
 - `newQuery` => `onNewQuery`
 - `saving` => `onSaving`
 - `saved` => `onSaved`
 - `deleting` => `onDeleting`
 - `deleted` => `onDeleted`

You can also insert your own custom hooks in the mapper like so
```php
public function someMapperMethod($arg_1, $arg_2){
    // perform something here that generates a $someValue
    $this->behaviours->apply($this, 'custom_hook', $someValue, $arg_1, $arg_2);
}
```
And the behaviours that would intercept the hook will just need to implement the `onCustomHook` method.

Check out the `Timestamps` and `Events` behaviour classes for inspiration.

Next: [Mapper events](mapper_events.md) 
