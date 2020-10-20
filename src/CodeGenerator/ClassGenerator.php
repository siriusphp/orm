<?php
declare(strict_types=1);

namespace Sirius\Orm\CodeGenerator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\PsrPrinter;
use Sirius\Orm\Definition\Mapper;
use Sirius\Orm\Definition\Orm;

class ClassGenerator
{
    /**
     * @var Orm
     */
    protected $orm;

    /**
     * @var PsrPrinter
     */
    protected $classPrinter;

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
            $files["{$name}_base_entity"] = $this->generateBaseEntityClass($mapper);
            $files["{$name}_entity"]      = $this->generateEntityClass($mapper);
        }

        return $files;
    }

    public function writeFiles()
    {
        if (!$this->orm->isValid()) {
            echo implode(PHP_EOL, $this->orm->getErrors());
            throw new \Exception('Invalid ORM specifications');
        }

        foreach ($this->getGeneratedClasses() as $class) {
            file_put_contents($class['path'], $class['contents']);
        }
    }

    private function generateBaseMapperClass(Mapper $mapper)
    {
        if (!$mapper->isValid()) {
            echo implode(PHP_EOL, $mapper->getErrors());
            throw new \Exception(sprintf('Specifications for %s mapper are not valid', $mapper->getName()));
        }

        $class = (new MapperBaseGenerator($mapper))->getClass();

        $file = new PhpFile();
        $file->setStrictTypes(true);

        return [
            'path'     => $mapper->getDestination() . $class->getName() . '.php',
            'contents' => $this->classPrinter->printFile($file)
                          . PHP_EOL
                          . ($class->getNamespace() ? $this->classPrinter->printNamespace($class->getNamespace()) : '')
                          . $this->classPrinter->printClass($class)
        ];
    }

    private function generateMapperClass(Mapper $mapper)
    {
        $file = new PhpFile();
        $file->setStrictTypes(true);

        $class = new ClassType(
            $mapper->getClassName(),
            new PhpNamespace($mapper->getNamespace())
        );

        $class->setExtends($mapper->getClassName() . 'Base');

        return [
            'path'     => $mapper->getDestination() . $class->getName() . '.php',
            'contents' => $this->classPrinter->printFile($file)
                          . PHP_EOL
                          . ($class->getNamespace() ? $this->classPrinter->printNamespace($class->getNamespace()) : '')
                          . $this->classPrinter->printClass($class)
        ];

    }

    private function generateBaseQueryClass(Mapper $mapper)
    {
        $class = (new QueryBaseGenerator($mapper))->getClass();

        $file = new PhpFile();
        $file->setStrictTypes(true);

        return [
            'path'     => $mapper->getDestination() . $class->getName() . '.php',
            'contents' => $this->classPrinter->printFile($file)
                          . PHP_EOL
                          . ($class->getNamespace() ? $this->classPrinter->printNamespace($class->getNamespace()) : '')
                          . $this->classPrinter->printClass($class)
        ];
    }

    private function generateQueryClass(Mapper $mapper)
    {
        $file = new PhpFile();
        $file->setStrictTypes(true);

        $queryClass = $mapper->getQueryClass();
        $class      = new ClassType(
            $queryClass,
            new PhpNamespace($mapper->getNamespace())
        );

        $class->setExtends($queryClass . 'Base');

        return [
            'path'     => $mapper->getDestination() . $class->getName() . '.php',
            'contents' => $this->classPrinter->printFile($file)
                          . PHP_EOL
                          . ($class->getNamespace() ? $this->classPrinter->printNamespace($class->getNamespace()) : '')
                          . $this->classPrinter->printClass($class)
        ];
    }

    private function generateBaseEntityClass(Mapper $mapper)
    {
        if ($mapper->getEntityStyle() === Mapper::ENTITY_STYLE_PROPERTIES) {
            $class = (new EntityBaseGeneratorUsingObjectProperties($mapper))->getClass();
        } else {
            $class = (new QueryBaseGenerator($mapper))->getClass();
        }

        $file = new PhpFile();
        $file->setStrictTypes(true);

        return [
            'path'     => $mapper->getEntityDestination() . $class->getName() . '.php',
            'contents' => $this->classPrinter->printFile($file)
                          . PHP_EOL
                          . ($class->getNamespace() ? $this->classPrinter->printNamespace($class->getNamespace()) : '')
                          . $this->classPrinter->printClass($class)
        ];
    }

    private function generateEntityClass(Mapper $mapper)
    {
        $file = new PhpFile();
        $file->setStrictTypes(true);

        $entityClass = $mapper->getEntityClass();
        $class       = new ClassType(
            $entityClass,
            new PhpNamespace($mapper->getEntityNamespace())
        );

        $class->setExtends($entityClass . 'Base');

        return [
            'path'     => $mapper->getEntityDestination() . $class->getName() . '.php',
            'contents' => $this->classPrinter->printFile($file)
                          . PHP_EOL
                          . ($class->getNamespace() ? $this->classPrinter->printNamespace($class->getNamespace()) : '')
                          . $this->classPrinter->printClass($class)
        ];

    }
}
