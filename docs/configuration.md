---
title: Configuration/DI | Sirius ORM
---

# ORM Configuration

#### Initialize the connection locator

```php
use Sirius\Orm\ConnectionLocator;
use Sirius\Orm\Connection;
use Sirius\Orm\CastingManager;
use Sirius\Orm\Relation\RelationBuilder;
use Sirius\Orm\Orm;

// the connection is a wrapper around a PDO instance
$connection = Connection::factory($dns, $username, $password, $options);

// the connection locator is a group of connections 
// that can be used for read/write
$connectionLocator = ConnectionLocator::new($connection);

// if you use only one connection you can do this directly
$connectionLocator = ConnectionLocator::new($dns, $username, $password, $options);
```

#### Initialize the relation builder (optional)

The relation builder will create relation objects when a mapper requests one from the ORM. 

If not provided, one is created for you.

```php
$relationBuilder = new RelationBuilder();
```

#### Initialize the casting manager (optional)

The [casting manager](the_casting_manager.md) is responsible for converting data between different types (strint to integer, array to json string etc). 

If not provided, one is created for you.

```php
$castingManager = new CastingManager();
```

#### Initialize the event dispatcher (optional)

You can use any <a href="https://www.php-fig.org/psr/psr-14/" target="_blank">PSR-14 event dispatcher</a>. Sirius ORM was tested using the amazing <a href="http://event.thephpleague.com/" target="_blank">league/event</a> so we recommend it.

If not provided, the [events](mapper_events.md) functionality will not be available.

```php
$eventDispatcher = new League\Event\Dispatcher();
```

#### Initialize the mapper locator (optional)

Mappers must be registered within the ORM and one way to register a mapper is by providing a class name (eg `$orm->register('products', app/Mapper/Product::class)`. In this case a mapper locator can be used to solve the instance.

If not provided, the "get mapper defined as class" feature will not be available

```php
// it can be the wrapper around a DI container 
// that implements the Sirius\Orm\Contract\MapperLocatorInterface
$mapperLocator = new MyMapperLocator($container);
```

## Building the ORM instance

```php
$orm = new Orm($connectionLocator, $relationBuilder, $castingManager, $eventDispatcher, $mapperLocator);
``` 

Next: [ORM usage](usage.md)
