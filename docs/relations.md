---
title: Relations | Sirius ORM
---


# Relations

Relations are defined during the mapper definition stage using a name and a relation definition like so:

```php
$productsDefinition->addRelation(
    'languages', 
    OneToMany::make('product_languages')
             ->setForeignKey('content_id'));
```

The code generation stage will initialize the relation in the mapper in the `initRelations()` method like so

```php
class ProductMapperBase extends Mapper {
    protected function initRelations()
    {
        $this->addRelation('languages', [
            'type' => 'one_to_many',
            'native_key' => 'id',
            'foreign_mapper' => 'product_languages',
            'foreign_key' => 'content_id',
            'load_strategy' => 'lazy',
        ]);
        //  rest of the relations go here
    }
}
```

In the mapper the relation is just an array of options that will be used by the `RelationBuilder` object to construct the actual relation when it is needed.

There is support for:

- [one-to-many relations](relation_one_to_many.md)
- [one-to-one relations](relation_one_to_one.md)
- [many-to-one relations](relation_many_to_one.md)
- [many-to-many relations](relation_many_to_many.md)
- [relation aggregates](relation_aggregate.md) for one-to-many and many-to-many

