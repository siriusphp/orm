---
title: Many to many relations | Sirius ORM
---

# "Many to many" relations

Also known as "__belongs to many__" relation in other ORMs. 

Example: many products belong to many tags. 

> **Important!** This relation is to be used only if the pivot table is simple.

For example, many products belong to many orders  via the  "order items" entity but, since the "order items" table is complex you will never want to attach/detach products to orders directly, which is something that you would do in the case of a "many products belong to many tags" kind of situation.


Below is a minimal example for defining a many-to-one relation

```php
use Sirius\Orm\Blueprint\Relation\ManyToMany;

$productsDefinition->addRelation(
    'tags', // name of the relation
    ManyToMany::make('tags') // name of the foreign mapper
);
``` 

### Methods available on the ManyToMany blueprint class

Method | Required | Purpose
:----- | :----| :----
setNativeKey()| No | Set the column in the native mapper. Defaults to the primary key of the native mapper
setForeignMapper()| No | Set the name of the foreign mapper
setForeignKey()| No | Set the column in the foreign mapper. Defaults to the primary key of the foreign mapper
setLoadStrategy()| No | Set's the loading strategy for the relation: lazy, eager (always load the related entities) or none (load only if expressly requested). <br>**Default: lazy**
setForeignGuards()| No | Sets query/entity guards. Read more [here](the_guards.md)
setQueryCallback()| No | A function to be applied to the query that retrieves the relations. This should be used mostly for sorting as anything more might give unexpeted results. 
 - | - | **Methods related to the pivot table**
setPivotTable()| No | Inferred from the names of the mappers (eg: products + tags => products_tags)
setPivotTableAlias()| No | To be used if the pivot table uses a prefix
setPivotNativeColumn()| No | The column in the pivot table corresponding to the native key. It is inferred from the native mapper's name (eg: products => product_id
setPivotForeignColumn()| No | The column in the pivot table corresponding to the native key. It is inferred from the foreign mapper's name (eg: tags => tags_id
setPivotColumns()| No | List of additional column-attribute pairs from the pivot table to be added as properties to the related entity (in this case, tags). One use case is using a 'position' column in the pivot to specify an order.
setPivotGuards()| No | Set query/entity [guards](the_guards.md) for the pivot table.

##### Use case for the pivot guards

One use case would be if the pivot table would store relations between tags and other entities (eg: categories). 

The pivot table would be something like 'links_to_tags' and have the following columns: tag_id, taggable_id (id of a product or a category), taggable_type ('product' or 'category')

In this case a `['type' => 'products']` pivot guard be added to the products-tags relation.

