# Limits and caveats

## 1. No entity manager

There is no central storage for all the rows extracted from the database to ensure all data is kept in sync.

If you have circular references and you access the relation a new SQL will be triggered. For example:
- products -> MANY_TO_ONE -> category
- category -> ONE_TO_MANY -> products

```php
$product = $orm->find('products', 1);
$product->get('name'); // returns 'old name'
$product->set('name', 'new name');
$productFromCategory = $product->get('category')->get('products')[0];
$productFromCategory->get('name'); // returns 'old name' NOT 'new name'
```

I believe this is a reasonable trade-off in the context of the request-response cycle. You usually either retrieve them for display OR modification. Plus, it's a bad practice do this in the first place.

## 2. Single database

At the moment the _Sirius ORM_ doesn't know how to handle relations over multiple databases. The SELECT queries that contain JOINs have to be on the same database.

