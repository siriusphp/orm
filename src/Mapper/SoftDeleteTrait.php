<?php
declare(strict_types=1);

namespace Sirius\Orm\Mapper;

use Sirius\Orm\Action\Delete;
use Sirius\Orm\Action\SoftDelete;
use Sirius\Orm\Action\Update;
use Sirius\Orm\Contract\EntityInterface;

trait SoftDeleteTrait
{
    protected $deletedAtColumn = 'deleted_at';

    public function newDeleteAction(EntityInterface $entity, $options)
    {
        $action = new SoftDelete($this, $entity, ['deleted_at_column' => $this->deletedAtColumn]);

        return $this->behaviours->apply($this, __FUNCTION__, $action);
    }

    public function forceDelete(EntityInterface $entity, $withRelations = false)
    {
        $this->assertCanPersistEntity($entity);

        $action = new Delete($this, $entity, ['relations' => $withRelations]);

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

    public function restore($pk)
    {
        $entity = $this->newQuery()
                       ->withTrashed()
                       ->where($this->getConfig()->getPrimaryKey(), $pk)
                       ->first();

        if ( ! $entity) {
            return false;
        }

        $this->getHydrator()->set($entity, $this->deletedAtColumn, null);
        $action = new Update($this, $entity);

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
