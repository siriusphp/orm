---
title: Mapper definition | Sirius ORM
---

# Mapper definition

Create a mapper definition and add it's specifications

```php
use Sirius\Orm\Blueprint\Mapper;

// `products` is the name this definition will be registered within the ORM definition
$productDefinition = Mapper::make('products'); 
```

#### Specs related to the mapper and query classes

Method | Required | Purpose
:----- | :----| :----
setNamespace($name)| No | The namespace of the mapper class. Defaults to that specified in the ORM definition
setDestination($path)| No | The folder where the mapper and query classes will be written. Defaults to that specified in the ORM definition
setClassName()| No | The name of the mapper class. It is inferred from the name (eg: products => ProductMapper)
addTrait()| No | Adds a PHP trait to be used by the mapper class
addBehaviour()| No | Adds a Blueprint behaviour to the mapper definition

> '_Blueprint_' behaviours are different than the '_Mapper_' behaviours. The '_Blueprint_' behaviours are used to intercept the generated classes and alter them by adding/modifying new properties, methods, traits.
The '_Mapper_' behaviours are used to intercept queries, entities and actions. 

You can check the 2 available '_Blueprint_' behaviours: **Timestamps** and **SoftDelete**.

#### Specs related to the table
Method | Required | Purpose
:----- | :----| :----
setTable()| No | It is inferred from the name (eg: products => products)
setTableAlias()| No | The alias of the table if you're using table prefixes. If the table is 'wp_products' the table alias should be 'products'
setPrimaryKey()| No | Sets the name of the primary key column.<br> **Default: id**

 
#### Specs related to the entity classes

Method | Required | Purpose
:----- | :----| :----
setEntityNamespace()| No | The namespace of the entity class. Defaults to that specified in the ORM definition
setEntityDestination()| No | The folder where the entity classes will be written. Defaults to that specified in the ORM definition
setEntityClassName()| No | The name of the mapper class. It is inferred from the name (eg: products => Product)

#### Specs related to the entity attributes, computed properties and relations

These will be detailed on the [entity definition](entity_definition.md) section.

Method | Required | Purpose
:----- | :----| :----
setEntityStyle()| No | It can be '**properties**' (uses `__get()` and `__set()`) or '**methods**' (uses getters and setters).<br> **Default: properties**
addAutoIncrementColumn()| No | Define a column that is of type auto-increment (primary key, big unsigned integer)  
addColumn()| Yes | You need to define the columns for your table. 
addComputedProperty()| No | Computed properties are entity attributes that are not directly linked to table columns
addRelation()| No | To be detailed on the relations section

You can find full examples in the repo's [tests/Generated/](https://github.com/siriusphp/orm/tree/master/tests/Generated) folder

Next: [Using mappers](mapper_usage.md) 
