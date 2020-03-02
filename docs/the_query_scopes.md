---
title: Query scopes | Sirius ORM
---

# Query scopes

Query scopes are functions that alter the queries and they are used to simplify the code (if they make lots of changes to the query) or express business rules.

The query scopes are attached to the mapper like so:

```php
use Sirius\Orm\MapperConfig;
use Sirius\Orm\Query;

$orm->register('articles', MapperConfig::fromArray([
    /**
     * other mapper config goes here 
     */
    MapperConfig::SCOPES => [
        'pickRandomlyFromThosePublished' => function(Query $query, $count = 5) {
            $query->where('published', true)
                  ->orderBy('RAND()')
                  ->limit($count);
            return $query;
        },
        'ownedByUser' => function(Query $query, User $user) {
            $query->where('author_id', $user->id);
            return $query;
        }
    ]
]));
```

which later can be used like so:

```php
$orm->select('articles')
    ->ownedByUser($someUser)
    ->pickRandomlyFromThosePublished(10);
```