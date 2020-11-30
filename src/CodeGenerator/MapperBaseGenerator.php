<?php
declare(strict_types=1);

namespace Sirius\Orm\CodeGenerator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Dumper;
use Nette\PhpGenerator\PhpNamespace;
use Sirius\Orm\Action\Delete;
use Sirius\Orm\Action\Insert;
use Sirius\Orm\Action\Update;
use Sirius\Orm\Blueprint\Mapper;
use Sirius\Orm\Blueprint\Relation;
use Sirius\Orm\Entity\ClassMethodsHydrator;
use Sirius\Orm\Entity\GenericHydrator;
use Sirius\Orm\Entity\StateEnum;
use Sirius\Orm\Exception\FailedActionException;
use Sirius\Orm\MapperConfig;

class MapperBaseGenerator
{
    /**
     * @var ClassType
     */
    protected $class;

    /**
     * @var PhpNamespace
     */
    protected $namespace;

    /**
     * @var Mapper
     */
    protected $mapper;
    /**
     * @var Dumper
     */
    protected $dumper;

    public function __construct(Mapper $mapper)
    {
        $this->dumper    = new Dumper();
        $this->mapper    = $mapper;
        $this->namespace = new PhpNamespace($mapper->getNamespace());
        $this->class     = new ClassType($mapper->getClassName() . 'Base', $this->namespace);
        $this->class->setAbstract(true);
    }

    public function getClass()
    {
        $this->build();

        return $this->class;
    }

    protected function build()
    {
        $this->namespace->addUse(\Sirius\Orm\Mapper::class);
        $this->namespace->addUse(FailedActionException::class);

        $this->class->setExtends('Mapper');

        $this->class->addComment(sprintf('@method %s where($column, $value, $condition)', $this->mapper->getQueryClass()));
        $this->class->addComment(sprintf('@method %s orderBy(string $expr, string ...$exprs)', $this->mapper->getQueryClass()));

        $this->addInitMethod();
        $this->addInitRelationsMethod();
        $this->addFindMethod();
        $this->addNewQueryMethod();
        $this->addSaveMethod();
        $this->addDeleteMethod();

        foreach ($this->mapper->getTraits() as $trait) {
            $this->class->addTrait($trait);
        }

        $this->class = $this->mapper->getOrm()->applyObservers($this->mapper->getName() . '_base_mapper', $this->class);
    }

    protected function addInitMethod()
    {
        $this->namespace->addUse(MapperConfig::class);

        $method = $this->class->addMethod('init')->setVisibility(ClassType::VISIBILITY_PROTECTED);

        $body = '$this->mapperConfig = MapperConfig::fromArray(';

        $config = [
            'entityClass'        => $this->mapper->getEntityNamespace() . '\\' . $this->mapper->getEntityClass(),
            'primaryKey'         => $this->mapper->getPrimaryKey(),
            'table'              => $this->mapper->getTable(),
            'tableAlias'         => $this->mapper->getTableAlias(),
            'guards'             => $this->mapper->getGuards(),
            'columns'            => [],
            'columnAttributeMap' => [],
            'casts'              => []
        ];

        $config = $this->mapper->getOrm()->applyObservers($this->mapper->getName() . '_mapper_config', $config);

        $body .= $this->dumper->dump($config);

        $body .= ');' . PHP_EOL;

        if ($this->mapper->getEntityStyle() == Mapper::ENTITY_STYLE_PROPERTIES) {
            $this->namespace->addUse(GenericHydrator::class);
            $body .= '$this->hydrator     = new GenericHydrator($this->orm->getCastingManager());' . PHP_EOL;
        } else {
            $this->namespace->addUse(ClassMethodsHydrator::class);
            $body .= '$this->hydrator     = new ClassMethodsHydrator($this->orm->getCastingManager());' . PHP_EOL;
        }
        $body .= '$this->hydrator->setMapper($this);' . PHP_EOL;

        $body .= PHP_EOL;

        $body .= '$this->initRelations();';

        $method->setBody($body);

        return $method;
    }

    protected function addInitRelationsMethod()
    {
        $method = $this->class->addMethod('initRelations')->setVisibility(ClassType::VISIBILITY_PROTECTED);

        $body = '';

        /** @var Relation $relation */
        foreach ($this->mapper->getRelations() as $name => $relation) {
            $body .= '$this->addRelation(\'' . $name . '\', ' . $this->dumper->dump($relation->toArray(), 4) . ');' . PHP_EOL;
            $body .= PHP_EOL;
        }

        $method->setBody($body);

        return $method;
    }

    protected function addFindMethod()
    {
        $this->namespace->addUse($this->mapper->getEntityNamespace() . '\\' . $this->mapper->getEntityClass());
        $method = $this->class->addMethod('find')
                              ->setReturnNullable(true)
                              ->setReturnType($this->mapper->getEntityClass());
        $method->addParameter('pk');
        $method->addParameter('load', [])->setType('array');
        $method->setBody('return $this->newQuery()->find($pk, $load);');
    }

    protected function addNewQueryMethod()
    {
        $method = $this->class->addMethod('newQuery')
                              ->setReturnType($this->mapper->getQueryClass());
        $method->addBody(sprintf('$query = new %s($this->getReadConnection(), $this);', $this->mapper->getQueryClass()));
        $method->addBody('return $this->behaviours->apply($this, __FUNCTION__, $query);');
    }

    protected function addSaveMethod()
    {
        $this->namespace->addUse(Insert::class, 'InsertAction');
        $this->namespace->addUse(Update::class, 'UpdateAction');
        $this->namespace->addUse(StateEnum::class);

        $method = $this->class->addMethod('save')
                              ->setReturnType('bool');
        $method->addParameter('entity')->setType($this->mapper->getEntityClass());
        $method->addParameter('withRelations', false);
        $method->setBody('
$action = $this->newSaveAction($entity, [\'relations\' => $withRelations]);

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
        ');

        $method = $this->class->addMethod('newSaveAction')->setReturnType('UpdateAction');
        $method->addParameter('entity')->setType($this->mapper->getEntityClass());
        $method->addParameter('options');
        $method->setBody('
if ( ! $this->getHydrator()->getPk($entity) || $entity->getState() == StateEnum::NEW) {
    $action = new InsertAction($this, $entity, $options);
} else {
    $action = new UpdateAction($this, $entity, $options);
}

return $this->behaviours->apply($this, __FUNCTION__, $action);
        ');
    }

    protected function addDeleteMethod()
    {
        $method = $this->class->addMethod('delete')
                              ->setReturnType('bool');
        $method->addParameter('entity')->setType($this->mapper->getEntityClass());
        $method->addParameter('withRelations', false);
        $method->setBody('
$action = $this->newDeleteAction($entity, [\'relations\' => $withRelations]);

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

        $this->namespace->addUse(Delete::class, 'DeleteAction');
        $method = $this->class->addMethod('newDeleteAction')->setReturnType('DeleteAction');
        $method->addParameter('entity')->setType($this->mapper->getEntityClass());
        $method->addParameter('options');
        $method->setBody('
$action = new DeleteAction($this, $entity, $options);

return $this->behaviours->apply($this, __FUNCTION__, $action);
        ');
    }
}
