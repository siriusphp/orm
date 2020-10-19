<?php
declare(strict_types=1);

namespace Sirius\Orm\Definition\Behaviour;

use Nette\PhpGenerator\ClassType;
use Sirius\Orm\Definition\Behaviour;
use Sirius\Orm\Definition\Column;
use Sirius\Orm\Definition\Mapper;

class SoftDelete extends Behaviour
{
    protected $deletedAtColumn = 'deleted_at';
    /**
     * @var Mapper
     */
    protected $mapper;

    static function make($deletedAtColumn = 'deleted_at')
    {
        return parent::make()->setDeletedAtColumn($deletedAtColumn);
    }

    function getName(): string
    {
        return 'soft_delete';
    }

    /**
     * @param string $deletedAtColumn
     *
     * @return SoftDelete
     */
    public function setDeletedAtColumn(string $deletedAtColumn): SoftDelete
    {
        $this->deletedAtColumn = $deletedAtColumn;

        return $this;
    }

    public function setMapper(Mapper $mapper): self
    {
        $this->mapper = $mapper;

        $columns = $mapper->getColumns();

        if ($this->deletedAtColumn && ! array_key_exists($this->deletedAtColumn, $columns)) {
            $mapper->addColumn(Column::datetime($this->deletedAtColumn)
                                     ->setNullable(true));
        }

        return $this;
    }

    public function observeBaseMapperClass(ClassType $class): ClassType
    {
        $class->getNamespace()->addUse(\Sirius\Orm\Action\SoftDelete::class, 'SoftDeleteAction');
        $class->getNamespace()->addUse(\Sirius\Orm\Action\Delete::class, 'DeleteAction');
        $class->getNamespace()->addUse(\Sirius\Orm\Action\Update::class, 'UpdateAction');
        $class->addProperty('deletedAtColumn', $this->deletedAtColumn)
              ->setVisibility('protected');

        //
        $method = $class->addMethod('newDeleteAction');
        $method->addParameter('entity')->setType($this->mapper->getEntityClass());
        $method->addParameter('options');
        $method->setBody('
$action = new SoftDeleteAction($this, $entity, [\'deleted_at_column\' => $this->deletedAtColumn]);

return $this->behaviours->apply($this, __FUNCTION__, $action);           
            ');

        $method = $class->addMethod('forceDelete');
        $method->addParameter('entity')->setType($this->mapper->getEntityClass());
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


        return parent::observeBaseMapperClass($class);
    }

    public function observeBaseQueryClass(ClassType $class): ClassType
    {
        $class->addProperty('deletedAtColumn', $this->deletedAtColumn)
              ->setVisibility('protected');

        // add guard
        if ( ! $class->hasMethod('init')) {
            $class->addMethod('init')
                  ->setVisibility(ClassType::VISIBILITY_PROTECTED)
                  ->setBody('parent::init();' . PHP_EOL);
        }
        $init = $class->getMethod('init');
        $init->addBody('$this->guards[] = $this->deletedAtColumn . \' IS NULL\';' . PHP_EOL);

        // add withTrashed()
        $class->addMethod('withTrashed')
              ->setVisibility(ClassType::VISIBILITY_PUBLIC)
              ->setBody('
$guards = [];
foreach ($this->guards as $k => $v) {
    if ($v != $this->deletedAtColumn . \' IS NULL\') {
        $guards[$k] = $v;
    }
}
$this->guards = $guards;

return $this;
            ');

        return parent::observeBaseQueryClass($class);
    }
}
