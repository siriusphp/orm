<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Mapper;

use Sirius\Orm\Behaviours;
use Sirius\Orm\ConnectionLocator;
use Sirius\Orm\Entity\GenericHydrator;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\QueryBuilder;
use Sirius\Orm\Tests\Generated\Entity\Category;

/**
 * @method CategoryQuery where($column, $value, $condition)
 * @method CategoryQuery orderBy(string $expr, string ...$exprs)
 */
abstract class CategoryMapperBase extends Mapper
{
    protected function init()
    {
        $this->queryBuilder      = QueryBuilder::getInstance();
        $this->behaviours        = new Behaviours();
        $this->mapperConfig      = MapperConfig::fromArray([
            'entityClass' => 'Sirius\Orm\Tests\Generated\Mapper\Category',
            'primaryKey' => 'id',
            'table' => 'categories',
            'tableAlias' => null,
            'guards' => [],
            'columns' => ['id', 'parent_id', 'position', 'name'],
            'columnAttributeMap' => [],
            'casts' => ['id' => 'int', 'parent_id' => 'int', 'position' => 'int', 'name' => 'string'],
        ]);
        $this->hydrator      = new GenericHydrator;

        $this->initRelations();
    }

    protected function initRelations()
    {
        $this->addRelation('parent', [
            'type' => 'many_to_one',
            'foreignKey' => 'id',
            'nativeKey' => 'id',
            'foreignMapper' => 'categories',
            'loadStrategy' => 'lazy',
        ]);

        $this->addRelation('children', [
            'type' => 'one_to_many',
            'nativeKey' => 'id',
            'foreignMapper' => 'categories',
            'foreignKey' => 'category_id',
            'loadStrategy' => 'lazy',
        ]);

        $this->addRelation('languages', [
            'type' => 'one_to_many',
            'nativeKey' => 'id',
            'foreignMapper' => 'languages',
            'foreignKey' => 'content_id',
            'foreignGuards' => ['content_type' => 'categories'],
            'loadStrategy' => 'lazy',
        ]);

        $this->addRelation('products', [
            'type' => 'one_to_many',
            'aggregates' => [
                'lowest_price' => ['function' => 'min(products.price)'],
                'highest_price' => ['function' => 'max(products.price)'],
            ],
            'nativeKey' => 'id',
            'foreignMapper' => 'products',
            'foreignKey' => 'category_id',
            'loadStrategy' => 'lazy',
        ]);
    }

    public function find($pk, array $load = []): ?Category
    {
        return parent::find($pk, $load);
    }

    public function newQuery(): CategoryQuery
    {
        $query = new CategoryQuery($this->getReadConnection(), $this);
        return $this->behaviours->apply($this, __FUNCTION__, $query);
    }

    public function save(Category $entity, $withRelations = false): bool
    {
        return parent::save($entity, $withRelations);
    }
}
