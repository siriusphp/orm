<?php
declare(strict_types=1);

namespace Sirius\Orm\CodeGenerator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Dumper;
use Nette\PhpGenerator\PhpNamespace;
use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Collection\PaginatedCollection;
use Sirius\Orm\Definition\Mapper;

class EntityBaseGenerator
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
        $this->namespace = new PhpNamespace($mapper->getEntityNamespace());
        $this->class     = new ClassType($mapper->getEntityClass() . 'Base', $this->namespace);
        $this->class->setAbstract(true);
    }

    public function getClass()
    {
        $this->build();

        return $this->class;
    }

    protected function build()
    {
        if ($this->mapper->getEntityStyle() === Mapper::ENTITY_STYLE_PROPERTIES) {
            $this->namespace->addUse(\Sirius\Orm\Entity\GenericEntity::class);
            $this->class->setExtends('GenericEntity');
        } else {
            $this->namespace->addUse(\Sirius\Orm\Entity\ClassMethodsEntity::class);
            $this->class->setExtends('ClassMethodsEntity');
        }

        $this->class = $this->mapper->observeBaseEntityClass($this->class);
    }

}