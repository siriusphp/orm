<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Mapper;

use Sirius\Orm\Behaviours;
use Sirius\Orm\Entity\GenericHydrator;
use Sirius\Orm\Exception\FailedActionException;
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
            'entityClass' => 'Sirius\Orm\Tests\Generated\Entity\EbayProduct',
            'primaryKey' => 'id',
            'table' => 'tbl_ebay_products',
            'tableAlias' => null,
            'guards' => [],
            'columns' => ['id', 'product_id', 'price', 'is_active'],
            'columnAttributeMap' => [],
            'casts' => ['id' => 'int', 'product_id' => 'int', 'price' => 'decimal:2', 'is_active' => 'bool'],
        ]);
        $this->hydrator      = new GenericHydrator;
        $this->hydrator->setMapperConfig($this->mapperConfig);

        $this->initRelations();
    }

    protected function initRelations()
    {
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

    public function delete(EbayProduct $entity, $withRelations = false): bool
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
