<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Mapper;

use Sirius\Orm\ConnectionLocator;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\Tests\Generated\Entity\Category;

/**
 * @method CategoryQuery where($column, $value, $condition)
 * @method CategoryQuery orderBy(string $expr, string ...$exprs)
 */
abstract class CategoryMapperBase extends Mapper
{
    public function __constructor(ConnectionLocator $connectionLocator)
    {
        parent::__construct($connectionLocator);
        $this->mapperConfig = MapperConfig::fromArray([
            'entityClass' => 'Sirius\Orm\Tests\Generated\Mapper\Category',
            'primaryKey' => 'id',
            'table' => 'categories',
            'tableAlias' => null,
            'columns' => ['id', 'parent_id', 'position', 'name'],
            'columnAttributeMap' => [],
            'casts' => ['id' => 'int', 'parent_id' => 'int', 'position' => 'int', 'name' => 'string'],
        ]);
    }

    public function find($pk, array $load = []): ?Category
    {
        return parent::find($pk, $load);
    }

    public function newQuery(): CategoryQuery
    {
        $query = $this->queryBuilder->newQuery($this->getReadConnection(), $this);
        return $this->behaviours->apply($this, __FUNCTION__, $query);
    }

    public function save(Category $entity, bool $withRelations = false): bool
    {
        return parent::save($entity, $withRelations);
    }
}
