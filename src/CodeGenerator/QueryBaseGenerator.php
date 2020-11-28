<?php
declare(strict_types=1);

namespace Sirius\Orm\CodeGenerator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Dumper;
use Nette\PhpGenerator\PhpNamespace;
use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Collection\PaginatedCollection;
use Sirius\Orm\Blueprint\Mapper;

class QueryBaseGenerator
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
        $this->class     = new ClassType($mapper->getQueryClass() . 'Base', $this->namespace);
        $this->class->setAbstract(true);
    }

    public function getClass()
    {
        $this->build();

        return $this->class;
    }

    protected function build()
    {
        $this->namespace->addUse(\Sirius\Orm\Query::class);
        $this->namespace->addUse($this->mapper->getEntityNamespace() . '\\' . $this->mapper->getEntityClass());

        $this->class->setExtends('Query');

        $this->addFirstMethod();
        $this->addGetMethod();
        $this->addPaginateMethod();

        $this->class = $this->mapper->getOrm()->applyObservers($this->mapper->getName() . '_base_query', $this->class);
    }

    private function addFirstMethod()
    {
        $method = $this->class->addMethod('first')
                              ->setReturnNullable(true)
                              ->setReturnType($this->mapper->getEntityClass());
        $method->setBody('return parent::first();');
    }

    private function addGetMethod()
    {
        $this->namespace->addUse(Collection::class);
        $method = $this->class->addMethod('get')
                              ->setReturnType('Collection');
        $method->setBody('return parent::get();');
        $method->addComment(sprintf('@return Collection|%s[]', $this->mapper->getEntityClass()));
    }

    private function addPaginateMethod()
    {
        $this->namespace->addUse(PaginatedCollection::class);
        $method = $this->class->addMethod('paginate')
                              ->setReturnType('PaginatedCollection');
        $method->addParameter('perPage')->setType('int');
        $method->addParameter('page', 1)->setType('int');
        $method->setBody('return parent::paginate($perPage, $page);');
        $method->addComment(sprintf('@return PaginatedCollection|%s[]', $this->mapper->getEntityClass()));
    }

}
