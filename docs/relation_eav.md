# Entity-Attribute-Value relations

EAV is a strategy of designing the database so that on one table (the native entity) you store known data about the entity and on another table (the "EAV table") you hold multiple rows each pointing to an attribute and value of the native entity
. The EAV table has one column for the name of the attribute and another with the value.

If you are not familiar with the EAV concept and you know Wordpress, the `post_meta` table is an EAV table with `meta_name` for the attribute name and `meta_value` as the value.

It is similar to "one to many" relations with the exception of how the matching rows are attached to the native entity. In the case of the "one to many relations" you attach all matching rows as a collection on the native entity (eg: one product
 has one collection of images). In the case of EAV relations each row is identified by a name and it is attached to the native entity as an attribute under that name.
 
This allows you to do things like

```php
$product->meta_title = "SEO title";
``` 

Given the fact that today's databases support JSON columns you can achieve the same result (ie: hold flexible data) using JSON columns. Still, it's a good feature for an ORM to provide.

This feature is WIP.