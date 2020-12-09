---
title: One to many relations | Sirius ORM
---

# "One to many" relations

Also known as "__has many__" relation in other ORM. 

Examples: 
- one product has many images, 
- one category has many products.

Bellow is the minimal code needed when definining a one-to-many relation.

```php
use Sirius\Orm\Blueprint\Relation\OneToMany;

$productsDefinition->addRelation(
    'images', // name of the relation
    OneToMany::make('images') // name of the foreign mapper
);
```

The relation will make some assumptions so that the relation configuration array looks like this:

```php
class ProductMapperBase extends Mapper {
    protected function initRelations() {
        $this->addRelation('languages', [
            'type' => 'one_to_many',
            'native_key' => 'id',
            'foreign_mapper' => 'product_languages',
            'foreign_key' => 'content_id',
            'load_strategy' => 'lazy',
        ]);
    }
}
```

### Methods available on the OneToMany blueprint class

Method | Required | Purpose
:----- | :----| :----
setForeignMapper()| No | Set the name of the foreign mapper
setNativeKey()| No | Set the column in the native mapper. Defaults to the primary key of the native mapper
setForeignKey()| No | Set the column in the foreign mapper. Determined based on the native mapper's name (eg: products => product_id)
setLoadStrategy()| No | Set's the loading strategy for the relation: lazy, eager (always load the related entities) or none (load only if expressly requested). <br>**Default: lazy**
setForeignGuards()| No | Sets query/entity guards. Read more [here](the_guards.md)
setQueryCallback()| No | A function to be applied to the query that retrieves the relations. This should be used mostly for sorting as anything more might give unexpeted results.
setCascade()| No | Sets whether to delete the  related entities when the main entity is deleted (true/false). <br>**Default: false** 


