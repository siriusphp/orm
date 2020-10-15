<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Mapper;

use Sirius\Orm\Behaviours;
use Sirius\Orm\ConnectionLocator;
use Sirius\Orm\Entity\GenericHydrator;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\QueryBuilder;
use Sirius\Orm\Tests\Generated\Entity\ProductLanguage;

/**
 * @method ProductLanguageQuery where($column, $value, $condition)
 * @method ProductLanguageQuery orderBy(string $expr, string ...$exprs)
 */
abstract class ProductLanguageMapperBase extends Mapper
{
    protected function init()
    {
        $this->queryBuilder      = QueryBuilder::getInstance();
        $this->behaviours        = new Behaviours();
        $this->mapperConfig      = MapperConfig::fromArray([
            'entityClass' => 'Sirius\Orm\Tests\Generated\Mapper\ProductLanguage',
            'primaryKey' => 'id',
            'table' => 'tbl_languages',
            'tableAlias' => null,
            'guards' => ['content_type' => 'products'],
            'columns' => ['id', 'content_type', 'content_id', 'lang', 'title', 'slug', 'description'],
            'columnAttributeMap' => [],
            'casts' => [
                'id' => 'int',
                'content_type' => 'string',
                'content_id' => 'int',
                'lang' => 'string',
                'title' => 'string',
                'slug' => 'string',
                'description' => 'string',
            ],
        ]);
        $this->hydrator      = new GenericHydrator;
    }

    public function find($pk, array $load = []): ?ProductLanguage
    {
        return parent::find($pk, $load);
    }

    public function newQuery(): ProductLanguageQuery
    {
        $query = $this->queryBuilder->newQuery($this->getReadConnection(), $this);
        return $this->behaviours->apply($this, __FUNCTION__, $query);
    }

    public function save(ProductLanguage $entity, $withRelations = false): bool
    {
        return parent::save($entity, $withRelations);
    }
}
