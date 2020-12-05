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
use Sirius\Orm\Tests\Generated\Entity\Tag;
use Sirius\Sql\Bindings;

/**
 * @method TagQuery where($column, $value, $condition)
 * @method TagQuery orderBy(string $expr, string ...$exprs)
 */
abstract class TagMapperBase extends Mapper
{
    protected function init()
    {
        $this->mapperConfig = MapperConfig::fromArray([
            'entityClass' => 'Sirius\Orm\Tests\Generated\Entity\Tag',
            'primaryKey' => 'id',
            'table' => 'tags',
            'tableAlias' => null,
            'guards' => [],
            'columns' => ['id', 'name'],
            'columnAttributeMap' => [],
            'casts' => ['id' => 'int', 'name' => 'string'],
            'pivotAttributes' => ['position_in_product'],
        ]);
        $this->hydrator     = new GenericHydrator($this->orm->getCastingManager());
        $this->hydrator->setMapper($this);

        $this->initRelations();
    }

    protected function initRelations()
    {
    }

    public function find($pk, array $load = []): ?Tag
    {
        return $this->newQuery()->find($pk, $load);
    }

    public function newQuery(): TagQuery
    {
        $query = new TagQuery($this->getReadConnection(), $this);
        return $this->behaviours->apply($this, __FUNCTION__, $query);
    }

    public function newSubselectQuery(Connection $connection, Bindings $bindings, string $indent): TagQuery
    {
        $query = new TagQuery($this->getReadConnection(), $this, $bindings, $indent);
        return $this->behaviours->apply($this, __FUNCTION__, $query);
    }

    public function save(Tag $entity, $withRelations = false): bool
    {
        $action = $this->newSaveAction($entity, ['relations' => $withRelations]);

        return $this->runActionInTransaction($action);
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

        return $this->runActionInTransaction($action);
    }

    public function newDeleteAction(Tag $entity, $options): DeleteAction
    {
        $action = new DeleteAction($this, $entity, $options);

        return $this->behaviours->apply($this, __FUNCTION__, $action);
    }
}
