<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Mapper;

use Sirius\Orm\ConnectionLocator;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\Tests\Generated\Entity\Language;

/**
 * @method LanguageQuery where($column, $value, $condition)
 * @method LanguageQuery orderBy(string $expr, string ...$exprs)
 */
abstract class LanguageMapperBase extends Mapper
{
    public function __constructor(ConnectionLocator $connectionLocator)
    {
        parent::__construct($connectionLocator);
        $this->mapperConfig = MapperConfig::fromArray([
            'entityClass' => 'Sirius\Orm\Tests\Generated\Mapper\Language',
            'primaryKey' => 'id',
            'table' => 'tbl_languages',
            'tableAlias' => null,
            'columns' => ['id', 'content_type', 'content_id', 'lang', 'title', 'description'],
            'columnAttributeMap' => [],
            'casts' => [
                'id' => 'int',
                'content_type' => 'string',
                'content_id' => 'int',
                'lang' => 'string',
                'title' => 'string',
                'description' => 'string',
            ],
        ]);
    }

    public function find($pk, array $load = []): ?Language
    {
        return parent::find($pk, $load);
    }

    public function newQuery(): LanguageQuery
    {
        $query = $this->queryBuilder->newQuery($this->getReadConnection(), $this);


        return $this->behaviours->apply($this, __FUNCTION__, $query);
    }

    public function save(Language $entity, bool $withRelations = false): bool
    {
        return parent::save($entity, $withRelations);
    }
}
