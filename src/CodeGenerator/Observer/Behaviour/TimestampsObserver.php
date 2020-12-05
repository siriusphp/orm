<?php
declare(strict_types=1);

namespace Sirius\Orm\CodeGenerator\Observer\Behaviour;

use Nette\PhpGenerator\ClassType;
use Sirius\Orm\Blueprint\Behaviour\Timestamps;
use Sirius\Orm\CodeGenerator\Observer\Base;

class TimestampsObserver extends Base
{
    /**
     * @var Timestamps
     */
    protected $behaviour;

    public function with(Timestamps $behaviour)
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
        $class->addProperty('createdAtColumn', $this->behaviour->getCreatedAtColumn())
              ->setVisibility('protected');
        $class->addProperty('updatedAtColumn', $this->behaviour->getUpdatedAtColumn())
              ->setVisibility('protected');

        if (! $class->hasMethod('init')) {
            $class->addMethod('init')->setVisibility('public')
                  ->setBody('parent::init();' . PHP_EOL);
        }
        /** @scrutinizer ignore-deprecated */ $class->getNamespace()->addUse(\Sirius\Orm\Behaviour\Timestamps::class);
        $method = $class->getMethod('init');
        $method->addBody(PHP_EOL . '$this->behaviours->add(new Timestamps($this->createdAtColumn, $this->updatedAtColumn));');

        return $class;
    }


    public function observeBaseQueryClass(ClassType $class): ClassType
    {
        $class->addProperty('createdAtColumn', $this->behaviour->getCreatedAtColumn())
              ->setVisibility('protected');
        $class->addProperty('updatedAtColumn', $this->behaviour->getUpdatedAtColumn())
              ->setVisibility('protected');

        // add methods
        $class->addMethod('orderByFirstCreated')
              ->setVisibility('public')
              ->setBody('
$this->orderBy($this->createdAtColumn . \' ASC\');

return $this;            
            ');
        $class->addMethod('orderByLastCreated')
              ->setVisibility('public')
              ->setBody('
$this->orderBy($this->createdAtColumn . \' DESC\');

return $this;            
            ');
        $class->addMethod('orderByFirstUpdated')
              ->setVisibility('public')
              ->setBody('
$this->orderBy($this->updatedAtColumn . \' ASC\');

return $this;            
            ');
        $class->addMethod('orderByLastCreated')
              ->setVisibility('public')
              ->setBody('
$this->orderBy($this->updatedAtColumn . \' DESC\');

return $this;            
            ');

        return $class;
    }
}
