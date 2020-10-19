<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Mapper;

use Sirius\Orm\Behaviours;
use Sirius\Orm\Entity\GenericHydrator;
use Sirius\Orm\Exception\FailedActionException;
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
            'entityClass' => 'Sirius\Orm\Tests\Generated\Entity\Category',
            'primaryKey' => 'id',
            'table' => 'categories',
            'tableAlias' => null,
            'guards' => [],
            'columns' => ['id', 'parent_id', 'position', 'name'],
            'columnAttributeMap' => [],
            'casts' => ['id' => 'int', 'parent_id' => 'int', 'position' => 'int', 'name' => 'string'],
        ]);
        $this->hydrator      = new GenericHydrator;
        $this->hydrator->setMapperConfig($this->mapperConfig);

        $this->initRelations();
    }

    protected function initRelations()
    {
        $this->addRelation('parent', [
            'type' => 'many_to_one',
            'foreign_key' => 'id',
            'native_key' => 'id',
            'foreign_mapper' => 'categories',
            'load_strategy' => 'lazy',
        ]);

        $this->addRelation('children', [
            'type' => 'one_to_many',
            'native_key' => 'id',
            'foreign_mapper' => 'categories',
            'foreign_key' => 'category_id',
            'load_strategy' => 'lazy',
        ]);

        $this->addRelation('languages', [
            'type' => 'one_to_many',
            'native_key' => 'id',
            'foreign_mapper' => 'languages',
            'foreign_key' => 'content_id',
            'foreign_guards' => ['content_type' => 'categories'],
            'load_strategy' => 'lazy',
        ]);

        $this->addRelation('products', [
            'type' => 'one_to_many',
            'aggregates' => [
                'lowest_price' => ['function' => 'min(products.price)'],
                'highest_price' => ['function' => 'max(products.price)'],
            ],
            'native_key' => 'id',
            'foreign_mapper' => 'products',
            'foreign_key' => 'category_id',
            'load_strategy' => 'lazy',
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
        $action = $this->newSaveAction($entity, ['relations' => $withRelations]);

        $this->connectionLocator->lockToWrite(true);
        $this->getWriteConnection()->beginTransaction();
        try {
            $action->run();
            $this->getWriteConnection()->commit();
            $this->connectionLocator->lockToWrite(false);

            return true;
        } catch (FailedActionException $e) {
            $this->getWriteConnection()->rollBack();
            $this->connectionLocator->lockToWrite(false);
            throw $e;
        }
    }

    public function delete(Category $entity, $withRelations = false): bool
    {
        $action = $this->newDeleteAction($entity, ['relations' => $withRelations]);

        $this->connectionLocator->lockToWrite(true);
        $this->getWriteConnection()->beginTransaction();
        try {
            $action->run();
            $this->getWriteConnection()->commit();

            return true;
        } catch (\Exception $e) {
            $this->getWriteConnection()->rollBack();
            throw $e;
        }
    }
}
