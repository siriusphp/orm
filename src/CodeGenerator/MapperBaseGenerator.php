<?php
declare(strict_types=1);

namespace Sirius\Orm\CodeGenerator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Dumper;
use Nette\PhpGenerator\PhpNamespace;
use Sirius\Orm\ConnectionLocator;
use Sirius\Orm\Definition\Mapper;
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

        $this->class->setExtends('Mapper');

        $this->class->addComment(sprintf('@method %s where($column, $value, $condition)', $this->mapper->getQueryClass()));
        $this->class->addComment(sprintf('@method %s orderBy(string $expr, string ...$exprs)', $this->mapper->getQueryClass()));

        $this->addConstructor();
        $this->addFindMethod();
        $this->addNewQueryMethod();
        $this->addSaveMethod();

        $this->class = $this->mapper->observeBaseMapperClass($this->class);
    }

    protected function addConstructor()
    {
        $this->namespace->addUse(ConnectionLocator::class);
        $this->namespace->addUse(MapperConfig::class);
        $method = $this->class->addMethod('__constructor');
        $method->addParameter('connectionLocator')->setType('ConnectionLocator');

        $body = 'parent::__construct($connectionLocator);' . PHP_EOL;
        $body .= '$this->mapperConfig = MapperConfig::fromArray(';

        $config = [
            'entityClass'        => $this->mapper->getNamespace() . '\\' . $this->mapper->getEntityClass(),
            'primaryKey'         => $this->mapper->getPrimaryKey(),
            'table'              => $this->mapper->getTable(),
            'tableAlias'         => $this->mapper->getTableAlias(),
            'columns'            => [],
            'columnAttributeMap' => [],
            'casts'              => []
        ];

        $config = $this->mapper->observeMapperConfig($config);

        $body .= $this->dumper->dump($config);

        $body .= ');' . PHP_EOL;

        $method->setBody($body);
    }

    protected function addFindMethod()
    {
        $this->namespace->addUse($this->mapper->getEntityNamespace() . '\\' . $this->mapper->getEntityClass());
        $method = $this->class->addMethod('find')
                            ->setReturnNullable(true)
                            ->setReturnType($this->mapper->getEntityClass());
        $method->addParameter('pk');
        $method->addParameter('load', [])->setType('array');
        $method->setBody('return parent::find($pk, $load);');
    }

    protected function addNewQueryMethod()
    {
        $method = $this->class->addMethod('newQuery')
                            ->setReturnType($this->mapper->getQueryClass());
        $method->addBody('$query = $this->queryBuilder->newQuery($this->getReadConnection(), $this);');
        $method->addBody('return $this->behaviours->apply($this, __FUNCTION__, $query);');


    }

    protected function addSaveMethod()
    {
        $method = $this->class->addMethod('save')
                            ->setReturnType('bool');
        $method->addParameter('entity')->setType($this->mapper->getEntityClass());
        $method->addParameter('withRelations', false)->setType('bool');
        $method->setBody('return parent::save($entity, $withRelations);');
    }
}
