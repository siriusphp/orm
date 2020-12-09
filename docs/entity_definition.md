---
title: Entity definition | Sirius ORM
---

# Entity definition

Assuming you already have a mapper definition

```php
use Sirius\Orm\Blueprint\Mapper;

// `products` is the name this definition will be registered within the ORM definition
$products = Mapper::make('products'); 
```

this is what you can do to add specs related to the entity.

##### Set the entity code style

```php
$products->setEntityStyle('properties' /* or 'methods' */);
```

##### Specify the primary key column

```php
$products->setPrimaryKey('products_id');  
```

The default value is **id**.

##### Specify an autoincrement column

```php
$products->addAutoIncrementColumn('id'); // `id` is the default 
```

The autoincrement column will also be set as the primary key.

##### Add columns/attributes

```php
$products->addColumn(Column::integer('category_id', true))
       ->addColumn(Column::varchar('sku')->setUnique(true))
       ->addColumn(Column::decimal('price', 14, 2)
                         ->setAttributeName('value')
                         ->setDefault(0))
       ->addColumn(Column::json('attributes'));
```

> We are using `addColumn` because most of the entity attributes are also columns we are using `Column` here. Also we plan to implement migrations.

The **types of columns available** (ie: the static methods in the `Column` class) are: varchar, bool, string, integer, tiny integer, small integer, big integer, date, datetime, timestamp, decimal, float.

Other things you can do to columns:

```php
// specify a different attribute name; 
// the column `value` is in the table, the attribute `name` is in the entity
$column = Column::decimal('value', 14, 2)->setAttributeName('price');

// specify the attribute cast, ie. the name of the casting function to be used when converting
$column = Column::datetime('created_at')->setAttributeCast('carbon_date');

// specify the attribute type; will be used for type-hinting
$column = Column::datetime('created_at')->setAttributeType(\Carbon\Carbon::class);
```

##### Add computed properties

```php
$products->addComputedProperty(
    ComputedProperty::make('discounted_price')
         ->setType('float')
         ->setGetterBody('return round($this->value * 0.9, 2);')
         ->setSetterBody('$this->value = $value / 0.9;')
);
```

You can ommit the getter or the setter for computed properties.

You can find full examples in the repo's [tests](https://github.com/siriusphp/orm/tree/master/tests/resources/definitions.php) folder

Next: [Using entities](entity_usage.md) 
