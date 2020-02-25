---
title: Relation aggregates | Sirius ORM
---

# Relation aggregates

Sometimes you want to query a relation and extract some aggregates. You may want to count the number of comments on a blog post, the average rating on a product etc. It would be faster if these aggregates are already available somewhere else (a
 stats table or in special columns) but sometimes your app doesn't need this type of optimizations.
 
The relation aggregates are available on "one to many" and "many to many" relations

Here's how to work with aggregates

#### Define the available aggregates for a relation

```php
use Sirius\Orm\Relation\RelationConfig;

$orm->register('products', MapperConfig::make(
    // other mapper config goes here
    'relations' => [
        'reviews' => [
            'type' => 'one_to_many',
            /**
             * other settings go here 
             */
            RelationConfig::AGGREGATES => [
                'reviews_count' => [
                    RelationConfig::AGG_FUNCTION => 'count(*)',
                    RelationConfig::AGG_CALLBACK => $functionThatChangesTheQuery
                ],
                'average_rating' => [
                    RelationConfig::AGG_FUNCTION => 'AVERAGE(rating)',
                    RelationConfig::LOAD_STRATEGY => RelationConfig::LOAD_EAGER, // load the aggregates immediately
                ],
            ]          
        ]       
    ]
));
```

#### Accessing aggregates

Using lazy loading

```php
$products = $orm->select('products')->limit(10)->get();

foreach ($products as $product) {
    echo $product->reviews_count;
    echo $product->average_rating;
}
```

or eager loading

```php
$products = $orm->select('products')
    ->load('reviews_count', 'average_rating')
    ->limit(10)
    ->get();

foreach ($products as $product) {
    echo $product->reviews_count;
    echo $product->average_rating;
}
```

## Complex aggregates

If you need more complex aggregates create queries using [joinWith]