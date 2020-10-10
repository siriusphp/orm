<?php
declare(strict_types=1);

namespace Sirius\Orm\CodeGenerator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\PsrPrinter;
use Sirius\Orm\Definition\Mapper;
use Sirius\Orm\Definition\Orm;
use Sirius\Orm\Query;

class ClassGenerator
{
    /**
     * @var Orm
     */
    protected $orm;

    protected $files = [];

    public function __construct(Orm $orm)
    {
        $this->orm          = $orm;
        $this->classPrinter = new PsrPrinter();
    }

    public function getGeneratedClasses()
    {
        $files = [];
        foreach ($this->orm->getMappers() as $name => $mapper) {
            $files["{$name}_base_mapper"] = $this->generateBaseMapperClass($mapper);
            $files["{$name}_mapper"]      = $this->generateMapperClass($mapper);
            $files["{$name}_base_query"]  = $this->generateBaseQueryClass($mapper);
            $files["{$name}_query"]       = $this->generateQueryClass($mapper);
            $files["{$name}_base_query"]  = $this->generateBaseEntityClass($mapper);
            $files["{$name}_query"]       = $this->generateEntityClass($mapper);
        }

        return $files;
    }

    public function writeFiles()
    {
        foreach ($this->getGeneratedClasses() as $class) {

        }
    }

    private function generateBaseMapperClass(Mapper $mapper)
    {
        $class = (new MapperBaseGenerator($mapper))->getClass();

        return [
            'path'     => $mapper->getDestination(),
            'contents' => $this->classPrinter->printClass($class)
        ];
    }

    private function generateMapperClass(Mapper $mapper)
    {
        $class = new ClassType(
            $mapper->getClassName(),
            new PhpNamespace($mapper->getNamespace())
        );

        $class->setExtends($mapper->getClassName() . 'Base');

        return [
            'path'     => $mapper->getDestination(),
            'contents' => $this->classPrinter->printClass($class)
        ];

    }

    private function generateBaseQueryClass(Mapper $mapper)
    {
        $class = new ClassType(
            $mapper->getClassName() . 'QueryBase',
            new PhpNamespace($mapper->getNamespace())
        );

        $class->setExtends(Query::class);

        return [
            'path'     => $mapper->getDestination(),
            'contents' => $this->classPrinter->printClass($class)
        ];
    }

    private function generateQueryClass(Mapper $mapper)
    {
        $queryClass = $mapper->getClassName() . 'Query';
        $class      = new ClassType(
            $queryClass,
            new PhpNamespace($mapper->getNamespace())
        );

        $class->setExtends($queryClass . 'Base');

        return [
            'path'     => $mapper->getDestination(),
            'contents' => $this->classPrinter->printClass($class)
        ];
    }

    private function generateBaseEntityClass(Mapper $mapper)
    {
        $class = new ClassType(
            $mapper->getClassName() . 'Base',
            new PhpNamespace($mapper->getNamespace())
        );

        $class->setExtends(\Sirius\Orm\Mapper::class);

        return [
            'path'     => $mapper->getDestination(),
            'contents' => $this->classPrinter->printClass($class)
        ];
    }

    private function generateEntityClass(Mapper $mapper)
    {
        $class = new ClassType(
            $mapper->getEntityClass(),
            new PhpNamespace($mapper->getEntityNamespace())
        );

        $class->setExtends($mapper->getEntityClass() . 'Base');

        return [
            'path'     => $mapper->getEntityDestination(),
            'contents' => $this->classPrinter->printClass($class)
        ];

    }
}
