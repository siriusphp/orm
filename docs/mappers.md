---
title: Mappers | Sirius ORM
---

# Mappers

In **Sirius ORM** mappers are objects called to:

- create [entities](entities.md)
- build [queries](queries.md) for retrieving entities
- persist entities

In order to keep the entities database-agnostic, it's the mapper's job to be aware of a lot of stuff: 
- the table and table columns 
- the relations between table columns and entity attributes
- the type of relations between the entity they handle and other entitie (eg: products-to-categories)

The things listed above are part of the "mapper configuration". As you will see from the code generated, each mapper has a `MapperConfig` object that holds this configuration.

Mappers have other responsibilites that are more or less hidden from you:
1. Mappers work with the ORM to be able to retrieve related entities from other mappers
2. Mappers work with an "entity hydrator" to allow converting arrays or table rows into entities
3. Mappers dispatch events, if the ORM has an event dispatcher
4. Mappers can be augmented using [behaviours](mapper_behaviours.md). Actually the `events` feature is implemented using behaviours

As mentioned [previously](code_generation.md), the **Sirius ORM** generates a list of classes based on the definition you provide.

Before learning about the mapper definition capabilities here a taste of the generated mapper base class

```php
namespace Sirius\Orm\Tests\Generated\Mapper;

use Sirius\Orm\Action\Delete as DeleteAction;
use Sirius\Orm\Action\Insert as InsertAction;
use Sirius\Orm\Action\SoftDelete as SoftDeleteAction;
use Sirius\Orm\Action\Update as UpdateAction;
use Sirius\Orm\Behaviour\Timestamps;
use Sirius\Orm\Connection;
use Sirius\Orm\Entity\GenericHydrator;
use Sirius\Orm\Entity\StateEnum;
use Sirius\Orm\Exception\FailedActionException;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\Tests\Generated\Entity\Product;
use Sirius\Sql\Bindings;

/**
 * @method ProductQuery where($column, $value, $condition)
 * @method ProductQuery orderBy(string $expr, string ...$exprs)
 */
abstract class ProductMapperBase extends Mapper
{
    protected $createdAtColumn = 'created_on';
    protected $updatedAtColumn = 'updated_on';
    protected $deletedAtColumn = 'deleted_on';

    protected function init()
    {
        $this->mapperConfig = MapperConfig::fromArray([
            'entityClass' => 'Sirius\Orm\Tests\Generated\Entity\Product',
            'primaryKey' => 'id',
            'table' => 'products',
            'guards' => [],
            'columns' => ['id', 'category_id', 'sku', 'price', 'attributes', 'created_on', 'updated_on', 'deleted_on'],
            'casts' => [
                'id' => 'int',
                'category_id' => 'int',
                'sku' => 'string',
                'price' => 'decimal:2',
                'attributes' => 'array',
                'created_on' => 'DateTime',
                'updated_on' => 'DateTime',
                'deleted_on' => 'DateTime',
            ],
        ]);
        $this->hydrator     = new GenericHydrator($this->orm->getCastingManager());
        $this->hydrator->setMapper($this);

        $this->initRelations();
        $this->behaviours->add(new Timestamps($this->createdAtColumn, $this->updatedAtColumn));
    }

    protected function initRelations()
    {
        $this->addRelation('languages', [
            'type' => 'one_to_many',
            'native_key' => 'id',
            'foreign_mapper' => 'product_languages',
            'foreign_key' => 'content_id',
            'load_strategy' => 'lazy',
        ]);

        /**
         * more relations go here
         */
    }

    /**
     * rest of the mapper's methods (find, newQuery, save, delete etc)
     */
}
```

You can find full examples in the repo's [tests/Generated/](https://github.com/siriusphp/orm/tree/master/tests/Generated) folder

## Extending mappers

The `xxxMapperBase` class is generated every time the code generation procedure is executed but the `xxxMapper` class is generated only the first time. 
Use this class to add the necessary behaviour required by your application.  

Next: [Mapper definition](mapper_definition.md) 
