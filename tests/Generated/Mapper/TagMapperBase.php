<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Mapper;

use Sirius\Orm\Behaviours;
use Sirius\Orm\ConnectionLocator;
use Sirius\Orm\Entity\GenericHydrator;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\QueryBuilder;
use Sirius\Orm\Tests\Generated\Entity\Tag;

/**
 * @method TagQuery where($column, $value, $condition)
 * @method TagQuery orderBy(string $expr, string ...$exprs)
 */
abstract class TagMapperBase extends Mapper
{
    protected function init()
    {
        $this->queryBuilder      = QueryBuilder::getInstance();
        $this->behaviours        = new Behaviours();
        $this->mapperConfig      = MapperConfig::fromArray([
            'entityClass' => 'Sirius\Orm\Tests\Generated\Mapper\Tag',
            'primaryKey' => 'id',
            'table' => 'tags',
            'tableAlias' => null,
            'guards' => [],
            'columns' => ['id', 'name'],
            'columnAttributeMap' => [],
            'casts' => ['id' => 'int', 'name' => 'string'],
        ]);
        $this->hydrator      = new GenericHydrator;

        $this->initRelations();
    }

    protected function initRelations()
    {
    }

    public function find($pk, array $load = []): ?Tag
    {
        return parent::find($pk, $load);
    }

    public function newQuery(): TagQuery
    {
        $query = new TagQuery($this->getReadConnection(), $this);
        return $this->behaviours->apply($this, __FUNCTION__, $query);
    }

    public function save(Tag $entity, $withRelations = false): bool
    {
        return parent::save($entity, $withRelations);
    }
}
