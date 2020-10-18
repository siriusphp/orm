<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Mapper;

use Sirius\Orm\Behaviours;
use Sirius\Orm\ConnectionLocator;
use Sirius\Orm\Entity\GenericHydrator;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\QueryBuilder;
use Sirius\Orm\Tests\Generated\Entity\Language;

/**
 * @method LanguageQuery where($column, $value, $condition)
 * @method LanguageQuery orderBy(string $expr, string ...$exprs)
 */
abstract class LanguageMapperBase extends Mapper
{
    protected function init()
    {
        $this->queryBuilder      = QueryBuilder::getInstance();
        $this->behaviours        = new Behaviours();
        $this->mapperConfig      = MapperConfig::fromArray([
            'entityClass' => 'Sirius\Orm\Tests\Generated\Mapper\Language',
            'primaryKey' => 'id',
            'table' => 'tbl_languages',
            'tableAlias' => null,
            'guards' => [],
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

        $this->initRelations();
    }

    protected function initRelations()
    {
    }

    public function find($pk, array $load = []): ?Language
    {
        return parent::find($pk, $load);
    }

    public function newQuery(): LanguageQuery
    {
        $query = new LanguageQuery($this->getReadConnection(), $this);
        return $this->behaviours->apply($this, __FUNCTION__, $query);
    }

    public function save(Language $entity, $withRelations = false): bool
    {
        return parent::save($entity, $withRelations);
    }
}
