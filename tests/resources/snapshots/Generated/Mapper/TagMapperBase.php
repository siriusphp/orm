<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Generated\Mapper;

use Sirius\Orm\Action\Insert as InsertAction;
use Sirius\Orm\Action\Update as UpdateAction;
use Sirius\Orm\Behaviours;
use Sirius\Orm\Entity\GenericHydrator;
use Sirius\Orm\Exception\FailedActionException;
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
            'entityClass' => 'Sirius\Orm\Tests\Generated\Entity\Tag',
            'primaryKey' => 'id',
            'table' => 'tags',
            'tableAlias' => null,
            'guards' => [],
            'columns' => ['id', 'name'],
            'columnAttributeMap' => [],
            'casts' => ['id' => 'int', 'name' => 'string'],
        ]);
        $this->hydrator      = new GenericHydrator;
        $this->hydrator->setMapperConfig($this->mapperConfig);

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

    public function newSaveAction(Tag $entity, $options): UpdateAction
    {
        if ( ! $this->getHydrator()->getPk($entity) || $entity->getState() == StateEnum::NEW) {
            $action = new InsertAction($this, $entity, $options);
        } else {
            $action = new UpdateAction($this, $entity, $options);
        }

        return $this->behaviours->apply($this, __FUNCTION__, $action);
    }

    public function delete(Tag $entity, $withRelations = false): bool
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

    public function newDeleteAction(Tag $entity, $options): UpdateAction
    {
        $action = new DeleteAction($this, $entity, $options);

        return $this->behaviours->apply($this, __FUNCTION__, $action);
    }
}
