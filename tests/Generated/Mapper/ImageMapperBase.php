<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Mapper;

use Sirius\Orm\Action\Delete as DeleteAction;
use Sirius\Orm\Action\Insert as InsertAction;
use Sirius\Orm\Action\Update as UpdateAction;
use Sirius\Orm\Connection;
use Sirius\Orm\Entity\GenericHydrator;
use Sirius\Orm\Entity\StateEnum;
use Sirius\Orm\Exception\FailedActionException;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\Tests\Generated\Entity\Image;
use Sirius\Sql\Bindings;

/**
 * @method ImageQuery where($column, $value, $condition)
 * @method ImageQuery orderBy(string $expr, string ...$exprs)
 */
abstract class ImageMapperBase extends Mapper
{
    protected function init()
    {
        $this->mapperConfig = MapperConfig::fromArray([
            'entityClass' => 'Sirius\Orm\Tests\Generated\Entity\Image',
            'primaryKey' => 'id',
            'table' => 'images',
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
        $this->hydrator     = new GenericHydrator($this->orm->getCastingManager());
        $this->hydrator->setMapper($this);

        $this->initRelations();
    }

    protected function initRelations()
    {
    }

    public function find($pk, array $load = []): ?Image
    {
        return $this->newQuery()->find($pk, $load);
    }

    public function newQuery(): ImageQuery
    {
        $query = new ImageQuery($this->getReadConnection(), $this);
        return $this->behaviours->apply($this, __FUNCTION__, $query);
    }

    public function newSubselectQuery(Connection $connection, Bindings $bindings, string $indent): ImageQuery
    {
        $query = new ImageQuery($this->getReadConnection(), $this, $bindings, $indent);
        return $this->behaviours->apply($this, __FUNCTION__, $query);
    }

    public function save(Image $entity, $withRelations = false): bool
    {
        $action = $this->newSaveAction($entity, ['relations' => $withRelations]);

        return $this->runActionInTransaction($action);
    }

    public function newSaveAction(Image $entity, $options): UpdateAction
    {
        if ( ! $this->getHydrator()->getPk($entity) || $entity->getState() == StateEnum::NEW) {
            $action = new InsertAction($this, $entity, $options);
        } else {
            $action = new UpdateAction($this, $entity, $options);
        }

        return $this->behaviours->apply($this, __FUNCTION__, $action);
    }

    public function delete(Image $entity, $withRelations = false): bool
    {
        $action = $this->newDeleteAction($entity, ['relations' => $withRelations]);

        return $this->runActionInTransaction($action);
    }

    public function newDeleteAction(Image $entity, $options): DeleteAction
    {
        $action = new DeleteAction($this, $entity, $options);

        return $this->behaviours->apply($this, __FUNCTION__, $action);
    }
}
