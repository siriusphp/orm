<?php

use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\DynamicMapper;
use Sirius\Orm\Query;

class ProductsMapper extends DynamicMapper
{

    public function __construct($connectionLocator)
    {

    }

    public function newEntity($data, $state = Orm::STATE_NEW): EntityInterface
    {
        return new Product($data, $state);
    }

    public function save(ProductEntity $entity): bool
    {

    }

    public function delete(ProductEntity $entity): bool
    {

    }

    public function newQuery(): ProductQuery {
        return new ProductQuery($this->getReadConnection());
    }

    public function new
}
