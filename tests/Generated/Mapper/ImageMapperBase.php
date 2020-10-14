<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Mapper;

use Sirius\Orm\Behaviours;
use Sirius\Orm\ConnectionLocator;
use Sirius\Orm\Entity\GenericHydrator;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\QueryBuilder;
use Sirius\Orm\Tests\Generated\Entity\Image;

/**
 * @method ImageQuery where($column, $value, $condition)
 * @method ImageQuery orderBy(string $expr, string ...$exprs)
 */
abstract class ImageMapperBase extends Mapper
{
    public function __constructor(ConnectionLocator $connectionLocator)
    {
        $this->connectionLocator = $connectionLocator;
        $this->queryBuilder      = QueryBuilder::getInstance();
        $this->behaviours        = new Behaviours();
        $this->mapperConfig      = MapperConfig::fromArray([
            'entityClass' => 'Sirius\Orm\Tests\Generated\Mapper\Image',
            'primaryKey' => 'id',
            'table' => 'tbl_images',
            'tableAlias' => null,
            'columns' => ['id', 'imageable_type', 'imageable_id', 'path', 'title', 'description'],
            'columnAttributeMap' => [],
            'casts' => [
                'id' => 'int',
                'imageable_type' => 'string',
                'imageable_id' => 'int',
                'path' => 'string',
                'title' => 'array',
                'description' => 'array',
            ],
        ]);
        $this->hydrator      = new GenericHydrator;
    }

    public function find($pk, array $load = []): ?Image
    {
        return parent::find($pk, $load);
    }

    public function newQuery(): ImageQuery
    {
        $query = $this->queryBuilder->newQuery($this->getReadConnection(), $this);
        return $this->behaviours->apply($this, __FUNCTION__, $query);
    }

    public function save(Image $entity, $withRelations = false): bool
    {
        return parent::save($entity, $withRelations);
    }
}
