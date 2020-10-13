<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Mapper;

use Sirius\Orm\ConnectionLocator;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\Tests\Generated\Entity\Tag;

/**
 * @method TagQuery where($column, $value, $condition)
 * @method TagQuery orderBy(string $expr, string ...$exprs)
 */
abstract class TagMapperBase extends Mapper
{
    public function __constructor(ConnectionLocator $connectionLocator)
    {
        parent::__construct($connectionLocator);
        $this->mapperConfig = MapperConfig::fromArray([
            'entityClass' => 'Sirius\Orm\Tests\Generated\Mapper\Tag',
            'primaryKey' => 'id',
            'table' => 'tags',
            'tableAlias' => null,
            'columns' => ['id', 'name'],
            'columnAttributeMap' => [],
            'casts' => ['id' => 'int', 'name' => 'string'],
        ]);
    }

    public function find($pk, array $load = []): ?Tag
    {
        return parent::find($pk, $load);
    }

    public function newQuery(): TagQuery
    {
        $query = $this->queryBuilder->newQuery($this->getReadConnection(), $this);
        return $this->behaviours->apply($this, __FUNCTION__, $query);
    }

    public function save(Tag $entity, bool $withRelations = false): bool
    {
        return parent::save($entity, $withRelations);
    }
}
