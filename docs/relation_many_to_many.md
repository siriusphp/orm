# "Many to many" relations

Also known as "__belongs to many__" relation in other ORM. Examples: many products belong to many tags. This involves a simple pivot table.

Other ORMs allow you to use another mapper object instead of the pivot table but my experience is that, if the relation is a little more complex, you will end up working with the relations directly. 

For example, many products belong to many orders  via the  "order items" but, since the "order items" table is heavily complex you will never want to attach/detach products to orders directly, which is something that you would do in the case of a
 "many products belong to many tags" kind of situation.
 
This ORM is build with a focus on DX and having less options to shoot yourself in the foot is a reasonable trade-off. 
 
Besides the options explained in the [relations page](relations.html) below you will find those specific to this "many to many" relations:

#### `through_table` / `RelationOptions::THROUGH_TABLE`

This is the table that links the entities together (products to tags)

#### `through_table_alias` / `RelationOptions::THROUGH_TABLE_ALIAS`

The table alias is useful if you are dealing with tables that have prefix

#### `through_table_alias` / `RelationOptions::THROUGH_NATIVE_COLUMN`

This refers to the column(s) that should match with the native primary key column(s)

#### `through_table_alias` / `RelationOptions::THROUGH_FOREIGN_COLUMN`

This refers to the column(s) that should match with the foreign primary key column(s)

#### `through_table_alias` / `RelationOptions::THROUGH_GUARDS`

Guards are columns that act as global filters (query conditions on top of other conditions). If you are using the same table to link multiple type of entities you can use this option to further filter the queries. You might have a "content_links"
 table that holds references between any type of content in your DB (products to tags, users to preferred activities). 
 
You might have a column called "native_id" that hold the ID of the product and "foreign_id" that holds the ID of the tag. However, for this set up to work, you would need some guards: `["native_type" => "product", "foreign_type" => "tag"]`
  
The guards are also used when creating/updating rows in the "through table" so you can safely link products and tags together via the global content links table.

#### `through_table_alias` / `RelationOptions::THROUGH_COLUMNS`

This option holds the list of columns that are available for modification in the "through table". For example you may link products to tags but also specify a `position` column in the through table to let you sort the tags by their position.

#### `through_table_alias` / `RelationOptions::THROUGH_COLUMNS_PREFIX`

This option is for attaching the "through columns" to the tag entity so you may change the. Defaults to `pivot_`. I don't know about other ORMs but Eloquent forces you to use the `->pivot` property of a foreign entity to do that. With Sirius Orm
 you can do just this
 
 ```php
$product->tags[0]->pivot_position = 10;
```