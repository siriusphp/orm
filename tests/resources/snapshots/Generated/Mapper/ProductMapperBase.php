<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Mapper;

use Sirius\Orm\Action\Delete as DeleteAction;
use Sirius\Orm\Action\Insert as InsertAction;
use Sirius\Orm\Action\SoftDelete as SoftDeleteAction;
use Sirius\Orm\Action\Update as UpdateAction;
use Sirius\Orm\Behaviour\Timestamps;
use Sirius\Orm\Behaviours;
use Sirius\Orm\Entity\GenericHydrator;
use Sirius\Orm\Exception\FailedActionException;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\QueryBuilder;
use Sirius\Orm\Tests\Generated\Entity\Product;

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
        $this->queryBuilder      = QueryBuilder::getInstance();
        $this->behaviours        = new Behaviours();
        $this->mapperConfig      = MapperConfig::fromArray([
            'entityClass' => 'Sirius\Orm\Tests\Generated\Entity\Product',
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
        $this->hydrator->setMapperConfig($this->mapperConfig);

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

        $this->addRelation('images', [
            'type' => 'one_to_many',
            'native_key' => 'id',
            'foreign_mapper' => 'images',
            'foreign_key' => 'imageable_id',
            'foreign_guards' => ['imageable_type' => 'products'],
            'load_strategy' => 'lazy',
            'cascade' => true,
        ]);

        $this->addRelation('tags', [
            'type' => 'many_to_many',
            'foreign_key' => 'id',
            'through_table' => 'tags_tbl_products',
            'through_table_alias' => 'products_to_tags',
            'through_guards' => ['tagable_type' => 'products'],
            'through_columns' => ['position' => 'position_in_product'],
            'through_native_column' => 'product_id',
            'through_foreign_column' => 'tag_id',
            'aggregates' => ['tags_count' => ['function' => 'count(tags.id)']],
            'native_key' => 'id',
            'foreign_mapper' => 'tags',
            'load_strategy' => 'lazy',
        ]);

        $this->addRelation('category', [
            'type' => 'many_to_one',
            'foreign_key' => 'id',
            'native_key' => 'id',
            'foreign_mapper' => 'categories',
            'load_strategy' => 'lazy',
        ]);

        $this->addRelation('ebay', [
            'type' => 'one_to_one',
            'native_key' => 'id',
            'foreign_mapper' => 'ebay_products',
            'foreign_key' => 'product_id',
            'load_strategy' => 'lazy',
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

    public function newSaveAction(Product $entity, $options): UpdateAction
    {
        if ( ! $this->getHydrator()->getPk($entity) || $entity->getState() == StateEnum::NEW) {
            $action = new InsertAction($this, $entity, $options);
        } else {
            $action = new UpdateAction($this, $entity, $options);
        }

        return $this->behaviours->apply($this, __FUNCTION__, $action);
    }

    public function delete(Product $entity, $withRelations = false): bool
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

    public function newDeleteAction(Product $entity, $options)
    {
        $action = new SoftDeleteAction($this, $entity, ['deleted_at_column' => $this->deletedAtColumn]);

        return $this->behaviours->apply($this, __FUNCTION__, $action);
    }

    public function forceDelete(Product $entity, $withRelations = false)
    {
        $action = new DeleteAction($this, $entity, ['relations' => $withRelations]);

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

    public function restore($pk): bool
    {
        $entity = $this->newQuery()
                       ->withTrashed()
                       ->find($pk);

        if ( ! $entity) {
            return false;
        }

        $this->getHydrator()->set($entity, $this->deletedAtColumn, null);
        $action = new UpdateAction($this, $entity);

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