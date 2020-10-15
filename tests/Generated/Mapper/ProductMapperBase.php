<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Mapper;

use Sirius\Orm\Behaviours;
use Sirius\Orm\ConnectionLocator;
use Sirius\Orm\Entity\GenericHydrator;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\Mapper\SoftDeleteTrait;
use Sirius\Orm\QueryBuilder;
use Sirius\Orm\Tests\Generated\Entity\Product;

/**
 * @method ProductQuery where($column, $value, $condition)
 * @method ProductQuery orderBy(string $expr, string ...$exprs)
 */
abstract class ProductMapperBase extends Mapper
{
    use SoftDeleteTrait;

    protected $createdAtColumn = 'created_on';
    protected $updatedAtColumn = 'updated_on';
    protected $deletedAtColumn = 'deleted_on';

    protected function init()
    {
        $this->queryBuilder      = QueryBuilder::getInstance();
        $this->behaviours        = new Behaviours();
        $this->mapperConfig      = MapperConfig::fromArray([
            'entityClass' => 'Sirius\Orm\Tests\Generated\Mapper\Product',
            'primaryKey' => 'id',
            'table' => 'tbl_products',
            'tableAlias' => null,
            'guards' => [],
            'columns' => ['id', 'sku', 'value', 'attributes', 'created_on', 'updated_on', 'deleted_on'],
            'columnAttributeMap' => ['value' => 'price'],
            'casts' => [
                'id' => 'int',
                'sku' => 'string',
                'value' => 'decimal:2',
                'attributes' => 'array',
                'created_on' => 'DateTime',
                'updated_on' => 'DateTime',
                'deleted_on' => 'DateTime',
            ],
        ]);
        $this->hydrator      = new GenericHydrator;
    }

    public function find($pk, array $load = []): ?Product
    {
        return parent::find($pk, $load);
    }

    public function newQuery(): ProductQuery
    {
        $query = $this->queryBuilder->newQuery($this->getReadConnection(), $this);
        return $this->behaviours->apply($this, __FUNCTION__, $query);
    }

    public function save(Product $entity, $withRelations = false): bool
    {
        return parent::save($entity, $withRelations);
    }
}
