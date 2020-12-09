---
title: Usage | Sirius ORM
---

# ORM Usage

The ORM instance is used for a 2 things:

##1. Registering a mapper

This can be done in a few ways:

##### Using a mapper instance
```php
// $productMapper as previously constructed
$orm->register('products', $productMapper);
```

##### Using a factory function
```php
$orm->register('products', function($orm) {
    // do something here that returns a mapper instance
});
```

#### Using a class
This only works if the ORM was constructed using a mapper locator (see [configuration](configuration.md#initialize-the-mapper-locator-optional))
```php
$orm->register('products', app\Mapper\Product::class);
```

##2. Retrieving a mapper

While your application parts (controllers, tasks etc) can depend on individual mapper sometimes you may want to dynamically retrieve the mappers. You can do this like so:

```php
$mapper = $orm->get('products');
```

Next: [Code generation](code_generation.md)
