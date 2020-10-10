<?php
declare(strict_types=1);

namespace Sirius\Orm\CodeGenerator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Sirius\Orm\Definition\Mapper;

class MapperBaseGenerator
{
    /**
     * @var ClassType
     */
    protected $class;

    /**
     * @var Mapper
     */
    private $mapper;

    public function __construct(Mapper $mapper)
    {
        $this->mapper = $mapper;
        $this->class  = new ClassType($mapper->getClassName() . 'Base', new PhpNamespace($mapper->getNamespace()));
    }

    public function getClass()
    {
        $this->build();

        return $this->class;
    }

    protected function build()
    {
        $this->class->setExtends(\Sirius\Orm\Mapper::class);
        $this->class->addProperty('primaryKey', $this->mapper->getConfig()->getPrimaryKey())->setVisibility(ClassType::VISIBILITY_PROTECTED);
    }
}
