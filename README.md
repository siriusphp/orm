
[![Source Code](http://img.shields.io/badge/source-siriusphp/orm-blue.svg?style=flat-square)](https://github.com/siriusphp/orm)
[![Latest Version](https://img.shields.io/packagist/v/siriusphp/orm.svg?style=flat-square)](https://github.com/siriusphp/orm/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/siriusphp/orm/blob/master/LICENSE)
[![Build Status](https://img.shields.io/travis/siriusphp/orm/master.svg?style=flat-square)](https://travis-ci.org/siriusphp/orm)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/siriusphp/orm.svg?style=flat-square)](https://scrutinizer-ci.com/g/siriusphp/orm/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/siriusphp/orm.svg?style=flat-square)](https://scrutinizer-ci.com/g/siriusphp/orm)

Sirius ORM is a fast yet flexible data mapper solution developed with DX in mind.

### Installation

```
composer require siriusphp/orm
```

### Initialization

```php
use Sirius\Orm\Orm;
use Sirius\Orm\ConnectionLocator;
$connectionLocator = ConnectionLocator::new(
    'mysql:host=localhost;dbname=testdb',
    'username',
    'password'
);
$orm = new Orm($connectionLocator);
```

### Configuration

AKA, registering mappers and relations

```php
$orm->register('pages', MapperConfig::fromArray([
    /**
     * here goes the configuration 
     */
]));

// continue with the rest of mappers
```

### Usage

```php
// find by ID
$page = $orm->find('pages', 1);
// or via the mapper
$page = $orm->get('pages')->find(1);

// query
$pages = $orm->select('pages')
             ->where('status', 'published')
             ->orderBy('date desc')
             ->limit(10)
             ->get();

// manipulate
$page->title = 'Best ORM evah!';
$page->featured_image->path = 'orm_schema.png';

// persist
$orm->save($page);
// or via the mapper
$orm->get('pages')->save($page);
```

### Links

- [Documentation](https://www.sirius.ro/php/sirius/orm/)