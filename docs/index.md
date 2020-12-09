---
title: Sirius ORM | Fast yet flexibile ORM built with DX in mind
---

# Sirius ORM

[![Source Code](http://img.shields.io/badge/source-siriusphp/orm-blue.svg?style=flat-square)](https://github.com/siriusphp/orm)
[![Latest Version](https://img.shields.io/packagist/v/siriusphp/orm.svg?style=flat-square)](https://github.com/siriusphp/orm/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/siriusphp/orm/blob/master/LICENSE)
[![Build Status](https://img.shields.io/travis/siriusphp/orm/master.svg?style=flat-square)](https://travis-ci.org/siriusphp/orm)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/siriusphp/orm.svg?style=flat-square)](https://scrutinizer-ci.com/g/siriusphp/orm/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/siriusphp/orm.svg?style=flat-square)](https://scrutinizer-ci.com/g/siriusphp/orm)

**Sirius ORM** is a [fast and lightweight](https://github.com/adrianmiu/forked-php-orm-benchmark) data mapper solution developed with DX in mind. It offers:                                                                                                  
1. Code generation of mapper, query and entity classes
2. Out-of-the-box support for complex relations and relation aggregates (COUNT/AVERAGE)
3. Eager-loading & lazy-loading (without increasing the number of queries)
4. Queries that let you JOIN with relations (not tables)
5. Entity patching
6. Deep persistence
7. Code completion support (due to code generation)
8. Speed & low memory usage (no Entity Manager)
9. 90+% code coverage

## Sneak-peak

Here's a preview of what you should expect your DX will be using this library:

#### Querying for entities
```php
$mapper = $container->get(app/Mapper/ProductMapper::class);

/** @var Collection|array[Product] $products */
$products = $mapper->where('price', 10, '>')
                   ->where('title', 'like', '%gold%')
                   ->get();

// same query can be written like this
$products = $mapper->newQuery()
                  ->applyFilters([
                    'price' => ['>' => 10],
                    'title' => ['contains' => 'gold'],
                  ])
                  ->get();
```

#### Manipulating an entity
```php
$mapper = $container->get(app/Mapper/ProductMapper::class);

/** @var app/Entity/Product $product */
$product = $mapper->find($_GET['id']); // obviously a bad practice

// patching = modifying an entity and its related entities
$product = $mapper->patch($product, $_POST); // obviously a bad practice

// deep save (true = save with all relations no matter the depth) 
$mapper->save($product, true); 
```

**Sirius ORM** uses mapper definition to generate the necessary classes (Mapper, Query and Entity). This allows for great code completion support, improved performance and extensibility. 

This library is build on the shoulder of giants that we like to acknowledge:
- [nette/php-generator](https://doc.nette.org/php-generator) - for code generation
- [doctrine/collections](https://www.doctrine-project.org/projects/doctrine-collections/en/1.6/index.html) - for entity collections returned by queries
- [atlas/pdo](http://atlasphp.io/cassini/pdo/) - for a simple and flexible solution to handle PDO connections
- [league/event](http://event.thephpleague.com/) - for the event dispatcher

Next: [Configuration](configuration.md)
