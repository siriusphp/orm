<?php
declare(strict_types=1);

namespace Sirius\Orm;

use Sirius\Orm\Contract\EntityInterface;
use Sirius\Orm\Exception\FailedActionException;

/**
 * @method Query where($column, $value, $condition)
 * @method Query orderBy(string $expr, string ...$exprs)
 */
class DynamicMapper extends Mapper
{
    /**
     * @param EntityInterface $entity
     * @param bool|array $withRelations relations to be also updated
     *
     * @return bool
     * @throws FailedActionException
     */
    public function save(EntityInterface $entity, $withRelations = false): bool
    {
        $this->assertCanPersistEntity($entity);
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

    /**
     * @param EntityInterface $entity
     * @param false $withRelations
     *
     * @return bool
     * @throws \Exception
     */
    public function delete(EntityInterface $entity, $withRelations = false)
    {
        $this->assertCanPersistEntity($entity);

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

    protected function assertCanPersistEntity($entity)
    {
        $entityClass = $this->mapperConfig->getEntityClass();
        if ( ! $entity || ! $entity instanceof $entityClass) {
            throw new \InvalidArgumentException(sprintf(
                'Mapper %s can only persist entity of class %s. %s class provided',
                __CLASS__,
                $entityClass,
                get_class($entity)
            ));
        }
    }
}
