<?php
declare(strict_types=1);

namespace Sirius\Orm;

use Sirius\Orm\Action\BaseAction;
use Sirius\Orm\Action\Delete;
use Sirius\Orm\Action\Insert;
use Sirius\Orm\Action\Update;
use Sirius\Orm\Contract\EntityInterface;
use Sirius\Orm\Entity\ClassMethodsEntity;
use Sirius\Orm\Entity\GenericEntity;
use Sirius\Orm\Entity\StateEnum;
use Sirius\Orm\Exception\FailedActionException;

/**
 * @method Query where($column, $value, $condition)
 * @method Query orderBy(string $expr, string ...$exprs)
 */
class DynamicMapper extends Mapper
{

    public static function make(Orm $orm, MapperConfig $mapperConfig)
    {
        $mapper               = new static($orm);
        $mapper->mapperConfig = $mapperConfig;

        if ( ! empty($mapperConfig->getBehaviours())) {
            $mapper->use(...$mapperConfig->getBehaviours());
        }

        $mapper->relations = $mapperConfig->getRelations();

        $mapper->hydrator->setMapperConfig($mapperConfig);

        return $mapper;
    }

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
     * @param $options
     *
     * @return Update
     */
    public function newSaveAction(EntityInterface $entity, $options)
    {
        if ( ! $this->getHydrator()->getPk($entity) || $entity->getState() == StateEnum::NEW) {
            $action = new Insert($this, $entity, $options);
        } else {
            $action = new Update($this, $entity, $options);
        }

        return $this->behaviours->apply($this, __FUNCTION__, $action);
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

    /**
     * @param EntityInterface $entity
     * @param $options
     *
     * @return BaseAction
     */
    public function newDeleteAction(EntityInterface $entity, $options)
    {
        $action = new Delete($this, $entity, $options);

        return $this->behaviours->apply($this, __FUNCTION__, $action);
    }

    /**
     * @param mixed $pk Value of the primary key
     * @param array $load Eager load relations
     *
     * @return null|EntityInterface|GenericEntity|ClassMethodsEntity
     */
    public function find($pk, array $load = []): ?EntityInterface
    {
        return $this->newQuery()->find($pk, $load);
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
