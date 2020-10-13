<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Mapper;

use Sirius\Orm\ConnectionLocator;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\Mapper\SoftDeleteTrait;
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

    public function __constructor(ConnectionLocator $connectionLocator)
    {
        parent::__construct($connectionLocator);
        $this->mapperConfig = MapperConfig::fromArray([
            'entityClass' => 'Sirius\Orm\Tests\Generated\Mapper\Product',
            'primaryKey' => 'id',
            'table' => 'tbl_products',
            'tableAlias' => null,
            'columns' => ['id', 'name', 'slug', 'description', 'price', 'attributes', 'created_on', 'updated_on', 'deleted_on'],
            'columnAttributeMap' => [],
            'casts' => [
                'id' => 'int',
                'name' => 'string',
                'slug' => 'string',
                'description' => 'string',
                'price' => 'decimal:2',
                'attributes' => 'array',
                'created_on' => 'DateTime',
                'updated_on' => 'DateTime',
                'deleted_on' => 'DateTime',
            ],
        ]);
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

    public function save(Product $entity, bool $withRelations = false): bool
    {
        return parent::save($entity, $withRelations);
    }
}
