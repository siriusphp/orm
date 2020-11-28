<?php
declare(strict_types=1);

namespace Sirius\Orm\CodeGenerator\Observer;

use Nette\PhpGenerator\ClassType;
use Sirius\Orm\Blueprint\ComputedProperty;
use Sirius\Orm\Blueprint\Mapper;
use Sirius\Orm\Helpers\Str;

class ComputedPropertyObserver extends Base
{
    /**
     * @var ComputedProperty
     */
    protected $property;

    public function with(ComputedProperty $column)
    {
        $clone           = clone($this);
        $clone->property = $column;

        return $clone;
    }

    public function observe(string $key, $object)
    {
        if ($key == $this->property->getMapper()->getName() . '_base_entity') {
            return $this->observeBaseEntity($object);
        }

        return $object;
    }

    public function __toString()
    {
        return sprintf('Observer for column %s of mapper %s', $this->property->getName(), $this->property->getMapper()->getName());
    }

    public function observeBaseEntity(ClassType $class): ClassType
    {
        $name = $this->property->getName();
        $type = $this->property->getType();

        if ($type && class_exists($type)) {
            $class->getNamespace()->addUse($type);
            $type = basename($type);
        }

        if ($this->property->getMapper()->getEntityStyle() === Mapper::ENTITY_STYLE_PROPERTIES) {
            $type .= $this->property->getNullable() ? '|null' : '';
            $class->addComment(sprintf('@property %s $%s', $type ?: 'mixed', $name));

            if (($body = $this->property->getSetterBody())) {
                $setter = $class->addMethod(Str::methodName($name . ' Attribute', 'set'));
                $setter->setVisibility(ClassType::VISIBILITY_PROTECTED);
                $setter->addParameter('value');
                $setter->addBody($body);
            }

            if (($body = $this->property->getGetterBody())) {
                $getter = $class->addMethod(Str::methodName($name . ' Attribute', 'get'));
                $getter->setVisibility(ClassType::VISIBILITY_PROTECTED);
                $getter->addBody($body);
            }
        } else {
            if (($body = $this->property->getSetterBody())) {
                $setter = $class->addMethod(Str::methodName($name . ' Attribute', 'set'));
                $setter->setVisibility(ClassType::VISIBILITY_PUBLIC);
                $setter->addParameter('value')
                       ->setType($type)
                       ->setNullable($this->property->getNullable());
                $setter->setBody($body);
                $setter->addComment($this->property->getSetterComment());
            }

            if (($body = $this->property->getGetterBody())) {
                $getter = $class->addMethod(Str::methodName($name . ' Attribute', 'get'));
                $getter->setVisibility(ClassType::VISIBILITY_PUBLIC);
                $getter->setBody($body);
                $getter->setReturnType($type);
                $getter->setReturnNullable($this->property->getNullable());
                $getter->addComment($this->property->getGetterComment());
            }
        }

        return $class;
    }
}
