---
title: Many to many relations | Sirius ORM
---

# "Many to many" relations

Also known as "__belongs to many__" relation in other ORMs. 

Example: many products belong to many tags. This involves a simple pivot table.

Other ORMs allow you to use another mapper object instead of the pivot table but my experience is that, if the relation is a little more complex, you will end up working with the relations directly. 

For example, many products belong to many orders  via the  "order items" but, since the "order items" table is complex you will never want to attach/detach products to orders directly, which is something that you would do in the case of a
 "many products belong to many tags" kind of situation.
 
This ORM is build with a focus on DX and having less options to shoot yourself in the foot is a reasonable trade-off. 
 
Besides the options explained in the [relations page](relations.html) below you will find those specific to this "many to many" relations:

> #### `through_table` / `RelationConfigs::THROUGH_TABLE`

> - This is the table that links the entities together (products to tags). 
> - It defaults to `{plural_of_table_1}_{plural_of_table_2}` where the tables are sorted alphabetically.
A pivot table between `products` and `tags` will be `products_tags` and a pivot between `products` and `categories` would be `categories_products`

> #### `through_table_alias` / `RelationConfigs::THROUGH_TABLE_ALIAS`

> - Not required. 
> - The table alias is useful if you are dealing with tables that have prefix. If your DB has a `tbl_products_tags` the alias should be `products_tags`

> #### `through_native_column` / `RelationConfigs::THROUGH_NATIVE_COLUMN`

> - This refers to the column(s) that should match with the native (ie: products) primary key column(s)

> #### `through_foreign_column` / `RelationConfigs::THROUGH_FOREIGN_COLUMN`

> - This refers to the column(s) that should match with the foreign (ie: tags) primary key column(s)

> #### `through_guards` / `RelationConfigs::THROUGH_GUARDS`

> - This refers to guards applied on the through table
> - You can read more about guards [here](the_guards.md). 
> - If you are using the same table to link multiple type of entities you can use this option to further filter the queries. 
You might have a "content_links"  table that holds references between any type of content in your DB (products to tags, users to preferred activities). 
> - You might have a column called `native_id` that hold the ID of the product and `foreign_id` that holds the ID of the tag. However, for this set up to work, you would need some guards: `["native_type" => "product", "foreign_type" => "tag"]`
> - The guards are also used when creating/updating rows in the "through table" so you can safely link products and tags together via the global content links table.

> #### `through_columns` / `RelationConfigs::THROUGH_COLUMNS`

> - This option holds the list of columns that are available for modification in the "through table". 
> - For example you may link products to tags but also specify a `position` column in the "through table" to let you sort the tags by their position.

> #### `through_columns_prefix` / `RelationConfigs::THROUGH_COLUMNS_PREFIX`

> - This option is for attaching the "through columns" to the tag entity so you may change the. 
Defaults to `pivot_`. 
> - I don't know about other ORMs but Eloquent forces you to use the `->pivot` property of a foreign entity to do that. 
With Sirius Orm you can do just this: `$product->tags[0]->pivot_position = 10;`


## Defining a many-to-many relation

```php
$orm->register('products', MapperConfig::fromArray([
    /**
     * other mapper config goes here
     */
    'relations' => [
        'tags' => [
            'name'                   => 'tags',
            'foreign_mapper'         => 'tags',
            'through_table'          => 'products_to_tags',
            'through_native_key'     => 'id',
            'through_foreign_key'    => 'id',
            'through_columns'        => ['position', 'created_at'],
            'through_columns_prefix' => 'link_',
            'query_callback'         => function($query) {
                $query->orderBy('position DESC');
                return $query;
             }
        ]       
    ]
]));
```