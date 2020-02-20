---
title: Direct queries | Sirius ORM
---

# Direct queries

Sometimes you want to execute queries on the mapper but you don't care for entities. For example you might want to count the number of comments per a list of articles, or get the average rating for some products

Constructing queries work the same as normal queries except the last part where you ask for the results

```php
$orm->get('reviews')
    ->join('products', 'products.id = reviews.product_id')
    ->where('products.name', 'Gold', 'contains')
    ->groupBy('product_id')
    ->select('product_id', 'AVERAGE(rating) as rating')
    ->fetchKeyPair();
```

Since Sirius\ORM uses Sirius\Sql which in turn uses Atlas\Connection which are glorified PDO connections the methods available on PDO statements for retrieving results are also available on the ORM queries:

- `fetchAll`
- `fetchColumn`(int $column = 0)
- `fetchGroup`(int $style = PDO::FETCH_COLUMN)
- `fetchKeyPair`()
- `fetchObject`(string $class = 'stdClass', array $args = [])
- `fetchObjects`(string $class = 'stdClass', array $args = [])
- `fetchOne`()
- `fetchUnique`()
- `fetchValue`(int $column = 0)

Here's another example for counting some matching rows:

```php
$orm->get('reviews')
    ->join('products', 'products.id = reviews.product_id')
    ->where('products.name', 'Gold', 'contains')
    ->select('COUNT(reviews.id) as total_gold_reviews')
    ->fetchValue();
```

## Reusing queries

You can reuse queries to minimise the potential for errors:

```php
$query = $orm->get('reviews')
             ->join('products', 'products.id = reviews.product_id')
             ->where('products.name', 'Gold', 'contains')
             ->groupBy('product_id');

$ratings = $query->select('product_id', 'AVERAGE(rating) as rating')->fetchKeyPair();

$count = $query->resetGroupBy()
                ->resetColumns()
                ->select('COUNT(reviews.id) as total_gold_reviews')
                ->fetchValue();
```