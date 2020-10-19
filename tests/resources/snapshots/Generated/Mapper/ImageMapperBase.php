<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Mapper;

use Sirius\Orm\Behaviours;
use Sirius\Orm\Entity\GenericHydrator;
use Sirius\Orm\Exception\FailedActionException;
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
    protected function init()
    {
        $this->queryBuilder      = QueryBuilder::getInstance();
        $this->behaviours        = new Behaviours();
        $this->mapperConfig      = MapperConfig::fromArray([
            'entityClass' => 'Sirius\Orm\Tests\Generated\Entity\Image',
            'primaryKey' => 'id',
            'table' => 'tbl_images',
            'tableAlias' => null,
            'guards' => [],
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
        $this->hydrator->setMapperConfig($this->mapperConfig);

        $this->initRelations();
    }

    protected function initRelations()
    {
    }

    public function find($pk, array $load = []): ?Image
    {
        return parent::find($pk, $load);
    }

    public function newQuery(): ImageQuery
    {
        $query = new ImageQuery($this->getReadConnection(), $this);
        return $this->behaviours->apply($this, __FUNCTION__, $query);
    }

    public function save(Image $entity, $withRelations = false): bool
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

    public function delete(Image $entity, $withRelations = false): bool
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
