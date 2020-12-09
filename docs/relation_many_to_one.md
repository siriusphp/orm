---
title: Many to one relations | Sirius ORM
---

# "Many to one" relations

Also known as "__belongs to__" relation in other ORMs. 

Examples: 
- multiple images belong to one product, 
- multiple products belong to a category 
- multiple pages belong to one parent.

Below is a minimal example for defining a many-to-one relation

```php
use Sirius\Orm\Blueprint\Relation\ManyToOne;

$imagesDefinition->addRelation(
    'product', // name of the relation
    ManyToOne::make('product') // name of the foreign mapper
);
``` 

### Methods available on the ManyToOne blueprint class

Method | Required | Purpose
:----- | :----| :----
setForeignMapper()| No | Set the name of the foreign mapper
setNativeKey()| No | Set the column in the native mapper. Determined based on the native mapper's name (eg: product => product_id)
setForeignKey()| No | Set the column in the foreign mapper. Defaults to the primary key of the foreign mapper
setLoadStrategy()| No | Set's the loading strategy for the relation: lazy, eager (always load the related entities) or none (load only if expressly requested). <br>**Default: lazy**
setForeignGuards()| No | Sets query/entity guards. Read more [here](the_guards.md)
setQueryCallback()| No | A function to be applied to the query that retrieves the relations. This should be used mostly for sorting as anything more might give unexpeted results.
