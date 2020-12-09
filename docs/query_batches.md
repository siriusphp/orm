---
title: Query batching | Sirius ORM
---

# Query batching

Entities may consume lots of memory so, when you need to process a lot of them, you may want to batch-process them.

For this you have to use the `chunk()` method of the query

```php
$productMapper->newQuery()
    ->chunk(100, function($product) {
        // do something with the product 
    });
```

There are times when you want to process a large numbers of entities but you know that, based on your query, there shouldn't be that many chunks to be processed. If that's the case, you can set a limit on the number of chunks to be processed.

 ```php
$productMapper->newQuery()
     ->chunk(100, function($product) {
         // do something with the product 
     }, 100 /** no more than 100 chunks */);
 ```

Next: [Direct queries](queries_direct.md) 
