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

        $this->initRelations();
    }

    protected function initRelations()
    {
        $this->addRelation('languages', [
            'type' => 'one_to_many',
            'nativeKey' => 'id',
            'foreignMapper' => 'product_languages',
            'foreignKey' => 'content_id',
            'loadStrategy' => 'lazy',
        ]);

        $this->addRelation('images', [
            'type' => 'one_to_many',
            'nativeKey' => 'id',
            'foreignMapper' => 'imagess',
            'foreignKey' => 'imageable_id',
            'foreignGuards' => ['imageable_type' => 'products'],
            'loadStrategy' => 'lazy',
            'cascade' => true,
        ]);

        $this->addRelation('tags', [
            'type' => 'many_to_many',
            'foreignKey' => 'id',
            'throughTable' => 'tags_tbl_products',
            'throughTableAlias' => 'products_to_tags',
            'throughGuards' => ['tagable_type' => 'products'],
            'throughColumns' => ['position' => 'position_in_product'],
            'throughNativeColumn' => 'product_id',
            'throughForeignColumn' => 'tag_id',
            'aggregates' => ['tags_count' => ['function' => 'count(tags.id)']],
            'nativeKey' => 'id',
            'foreignMapper' => 'tags',
            'loadStrategy' => 'lazy',
        ]);

        $this->addRelation('category', [
            'type' => 'many_to_one',
            'foreignKey' => 'id',
            'nativeKey' => 'id',
            'foreignMapper' => 'categories',
            'loadStrategy' => 'lazy',
        ]);

        $this->addRelation('ebay', [
            'type' => 'one_to_one',
            'nativeKey' => 'id',
            'foreignMapper' => 'ebay_products',
            'foreignKey' => 'product_id',
            'loadStrategy' => 'lazy',
        ]);
    }

    public function find($pk, array $load = []): ?Product
    {
        return parent::find($pk, $load);
    }

    public function newQuery(): ProductQuery
    {
        $query = new ProductQuery($this->getReadConnection(), $this);
        return $this->behaviours->apply($this, __FUNCTION__, $query);
    }

    public function save(Product $entity, $withRelations = false): bool
    {
        return parent::save($entity, $withRelations);
    }
}
