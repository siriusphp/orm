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

        if (is_string($type) && (class_exists($type) || strpos($type, '\\') !== false)) {
            $class->getNamespace()->addUse($type, null, $alias);
        } else {
            $alias = $type;
        }

        if ($this->property->getMapper()->getEntityStyle() === Mapper::ENTITY_STYLE_PROPERTIES) {
            $class->addComment(sprintf('@property %s $%s',
                $alias ? $alias . ($this->property->getNullable() ? '|null' : '') : 'mixed',
                $name));

            if (($body = $this->property->getSetterBody())) {
                $setter = $class->addMethod(Str::methodName($name . ' Attribute', 'set'));
                $setter->setVisibility(ClassType::VISIBILITY_PROTECTED);
                $setter->addParameter('value')
                       ->setNullable($this->property->getNullable())
                       ->setType($alias);
                $setter->setBody($body);
                $setter->setComment($this->property->getSetterComment());
            }

            if (($body = $this->property->getGetterBody())) {
                $getter = $class->addMethod(Str::methodName($name . ' Attribute', 'get'));
                $getter->setVisibility(ClassType::VISIBILITY_PROTECTED);
                $getter->setReturnType($alias);
                $getter->setReturnNullable($this->property->getNullable());
                $getter->setBody($body);
                $getter->setComment($this->property->getGetterComment());
            }
        } else {
            if (($body = $this->property->getSetterBody())) {
                $setter = $class->addMethod(Str::methodName($name, 'set'));
                $setter->setVisibility(ClassType::VISIBILITY_PUBLIC);
                $setter->addParameter('value')
                       ->setType($type)
                       ->setNullable($this->property->getNullable());
                $setter->setBody($body);
                $setter->setComment($this->property->getSetterComment());
            }

            if (($body = $this->property->getGetterBody())) {
                $getter = $class->addMethod(Str::methodName($name, 'get'));
                $getter->setVisibility(ClassType::VISIBILITY_PUBLIC);
                $getter->setBody($body);
                $getter->setReturnType($type);
                $getter->setReturnNullable($this->property->getNullable());
                $getter->setComment($this->property->getGetterComment());
            }
        }

        return $class;
    }
}
