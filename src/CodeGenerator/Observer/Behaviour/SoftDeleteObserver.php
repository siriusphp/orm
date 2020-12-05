<?php
declare(strict_types=1);

namespace Sirius\Orm\CodeGenerator\Observer\Behaviour;

use Nette\PhpGenerator\ClassType;
use Sirius\Orm\Blueprint\Behaviour\SoftDelete;
use Sirius\Orm\Blueprint\Behaviour\Timestamps;
use Sirius\Orm\CodeGenerator\Observer\Base;
use Sirius\Orm\Query\SoftDeleteTrait;

class SoftDeleteObserver extends Base
{
    /**
     * @var SoftDelete
     */
    protected $behaviour;

    public function with(SoftDelete $behaviour)
    {
        $clone            = clone($this);
        $clone->behaviour = $behaviour;

        return $clone;
    }


    public function observe(string $key, $object)
    {
        if ($key == $this->behaviour->getMapper()->getName() . '_base_mapper') {
            return $this->observeBaseMapperClass($object);
        }
        if ($key == $this->behaviour->getMapper()->getName() . '_base_query') {
            return $this->observeBaseQueryClass($object);
        }

        return $object;
    }

    public function __toString()
    {
        return sprintf(
            'Observer for behaviour %s of mapper %s',
            $this->behaviour->getName(),
            $this->behaviour->getMapper()->getName()
        );
    }



    public function observeBaseMapperClass(ClassType $class): ClassType
    {
        $class->getNamespace()->addUse(\Sirius\Orm\Action\SoftDelete::class, 'SoftDeleteAction');
        $class->getNamespace()->addUse(\Sirius\Orm\Action\Delete::class, 'DeleteAction');
        $class->getNamespace()->addUse(\Sirius\Orm\Action\Update::class, 'UpdateAction');
        $class->addProperty('deletedAtColumn', $this->behaviour->getDeletedAtColumn())
              ->setVisibility('protected');

        //
        $method = $class->addMethod('newDeleteAction');
        $method->addParameter('entity')->setType($this->behaviour->getMapper()->getEntityClass());
        $method->addParameter('options');
        $method->setBody('
$options = array_merge((array) $options, [\'deleted_at_column\' => $this->deletedAtColumn]);         
$action = new SoftDeleteAction($this, $entity, $options);

return $this->behaviours->apply($this, __FUNCTION__, $action);           
            ');

        $method = $class->addMethod('forceDelete');
        $method->addParameter('entity')->setType($this->behaviour->getMapper()->getEntityClass());
        $method->addParameter('withRelations', false);
        $method->setBody('
$action = new DeleteAction($this, $entity, [\'relations\' => $withRelations]);

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
        ');

        $method = $class->addMethod('restore')->setReturnType('bool');
        $method->addParameter('pk');
        $method->setBody('
$entity = $this->newQuery()
               ->withTrashed()
               ->find($pk);

if ( ! $entity) {
    return false;
}

$this->getHydrator()->set($entity, $this->deletedAtColumn, null);
$action = new UpdateAction($this, $entity);

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
        ');

        return $class;
    }

    public function observeBaseQueryClass(ClassType $class): ClassType
    {
        $class->addProperty('deletedAtColumn', $this->behaviour->getDeletedAtColumn())
              ->setVisibility('protected');

        // add guard
        if (! $class->hasMethod('init')) {
            $class->addMethod('init')
                  ->setVisibility(ClassType::VISIBILITY_PROTECTED)
                  ->setBody('parent::init();' . PHP_EOL);
        }
        $init = $class->getMethod('init');
        $init->addBody('$this->withoutTrashed();' . PHP_EOL);

        $class->getNamespace()->addUse(SoftDeleteTrait::class, null, $traitAlias);

        $class->addTrait($traitAlias);

        return $class;
    }
}
