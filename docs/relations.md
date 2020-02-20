---
title: Relations | Sirius ORM
---


# Relations

Relations have a _name_ (the attribute of the entity that will get the relation), a _native mapper_ (the left-hand side of the relations), a _foreign mapper_ and some options.

If you pass `MapperConfig` instances to the ORM, which is the easiest way to go, you have to populate the `relations`

```php
$orm->register('products', MapperConfig::make(
    // other mapper config goes here
    'relations' => [
        'category' => [
            'type' => 'many_to_one',
            'foreign_mapper' => 'categories',
            // this is enough if you follow the conventions
        ]       
    ]
));
```

Relations are constructed by the ORM on the first request using the configuration provided. 
This means, if a relation is not used, it won't be constructed. 

## Configuration and conventions

These are the general configuration options for relations. Check each relation dedicated page for specific additional options and details. 

You can use the `RelationConfig` class constants to make sure you're not making any mistakes when defining the relations

> #### `type` / `RelationConfig::TYPE`

> - holds the type of relation since relations are created on demand
> - supported types: `one_to_one`, `one_to_many`, `many_to_one`, `many_to_many`, `many_to_many_through`, `eav`, `aggregate`

> #### `foreign_mapper` / `RelationConfig::FOREIGN_MAPPER`

> - the name of the mapper as registered in the ORM

> #### `native_key` / `RelationConfig::NATIVE_KEY`

> - is the column(s) in the native mapper that holds the **values** to be searched in the foreign mapper  
> - the default value depends on the relation type. 
>     - for **many to one**: `{foreign mapper table at singular}_{id column of the foreign mapper}` (eg: `category_id` for products -> many to one -> category)
>     - for the rest: `{id of the native mapper}` (eg: `id` for category -> one to many -> products)

> #### `foreign_key` / `RelationConfig::FOREIGN_KEY`

> - is the column(s) in the foreign mapper that will be used to query and select the foreign entities 
> - the default value depends on the relation type. 
>     - for **one to many**: `{native mapper table at singular}_{id column of the native mapper}` (eg: `category_id` for category -> one to many -> products)
>     - for the rest: `{id of the native mapper}` (eg: `id` for products -> many to one -> category)

> #### `foreign_guards` / `RelationConfig::FOREIGN_GUARDS`

> - learn more about guards [here](the_guards.md)
> - this is useful if the foreign mapper holds multiple types of "content" (eg: a "content" table holds both "pages" and "articles" and uses column "content_type" to determine which is which). The same end goal can be achieved by creating specific
 mappers (eg: a "articles" mapper + a "pages" mapper instead of a single "content" mapper) 

> #### `load_strategy` / `RelationConfig::LOAD_STRATEGY`

> - by default all relations are loaded lazy since 1) it doesn't affect the number of executed queries and 2) you can specify which relations to be eager loaded on each query
> - however, if you find situations, where you need to have some relations always present it could save you some time 
> - you can also set it to `none` if you want for the relation to be loaded ONLY when you specify it (ie: ONLY EAGER)

> #### `cascade` / `RelationConfig::CASCADE`

> - this specifies the behaviour for DELETE operations (ie: also delete related entities)
> - by default the cascade option is `false`

> #### `query_callback` / `RelationConfig::QUERY_CALLBACK`

> - this is for situations where the _foreign guards_ option is not enough you can use a function to executed on the query before retrieving the related entities
> - unlike the _foreign guards_ which are applied on subsequent SAVE operations the _query callback_ is for retrieval only. 
You can use this for sorting or limiting the results. 

