<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Mapper;

use Sirius\Orm\Behaviours;
use Sirius\Orm\ConnectionLocator;
use Sirius\Orm\Entity\GenericHydrator;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\QueryBuilder;
use Sirius\Orm\Tests\Generated\Entity\EbayProduct;

/**
 * @method EbayProductQuery where($column, $value, $condition)
 * @method EbayProductQuery orderBy(string $expr, string ...$exprs)
 */
abstract class EbayProductMapperBase extends Mapper
{
    protected function init()
    {
        $this->queryBuilder      = QueryBuilder::getInstance();
        $this->behaviours        = new Behaviours();
        $this->mapperConfig      = MapperConfig::fromArray([
            'entityClass' => 'Sirius\Orm\Tests\Generated\Mapper\EbayProduct',
            'primaryKey' => 'id',
            'table' => 'tbl_ebay_products',
            'tableAlias' => null,
            'guards' => [],
            'columns' => ['id', 'product_id', 'price', 'is_active'],
            'columnAttributeMap' => [],
            'casts' => ['id' => 'int', 'product_id' => 'int', 'price' => 'decimal:2', 'is_active' => 'bool'],
        ]);
        $this->hydrator      = new GenericHydrator;
    }

    public function find($pk, array $load = []): ?EbayProduct
    {
        return parent::find($pk, $load);
    }

    public function newQuery(): EbayProductQuery
    {
        $query = new EbayProductQuery($this->getReadConnection(), $this);
        return $this->behaviours->apply($this, __FUNCTION__, $query);
    }

    public function save(EbayProduct $entity, $withRelations = false): bool
    {
        return parent::save($entity, $withRelations);
    }
}
