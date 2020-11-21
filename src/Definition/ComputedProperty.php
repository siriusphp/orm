<?php
declare(strict_types=1);

namespace Sirius\Orm\Definition;

use Nette\PhpGenerator\ClassType;
use Sirius\Orm\Helpers\Str;

class ComputedProperty extends Base
{
    use MapperAwareTrait;

    protected $name;

    protected $type;

    protected $setterBody;

    protected $getterBody;

    static function make($name = '')
    {
        return (new static)->setName($name);
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return ComputedProperty
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSetterBody()
    {
        return $this->setterBody;
    }

    /**
     * @param mixed $setterBody
     *
     * @return ComputedProperty
     */
    public function setSetterBody($setterBody)
    {
        $this->setterBody = $setterBody;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getGetterBody()
    {
        return $this->getterBody;
    }

    /**
     * @param mixed $getterBody
     *
     * @return ComputedProperty
     */
    public function setGetterBody($getterBody)
    {
        $this->getterBody = $getterBody;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     *
     * @return ComputedProperty
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function observeBaseEntityClass(ClassType $class): ClassType
    {
        $name = $this->getName();
        $type = $this->getType();

        if ($type && class_exists($type)) {
            $class->getNamespace()->addUse($type);
            $type = basename($type);
        }

        if ($this->mapper->getEntityStyle() === Mapper::ENTITY_STYLE_PROPERTIES) {
            $class->addComment(sprintf('@property %s $%s', $type ?: 'mixed', $name));

            if (($body = $this->getSetterBody())) {
                $setter = $class->addMethod(Str::methodName($name . ' Attribute', 'set'));
                $setter->setVisibility(ClassType::VISIBILITY_PROTECTED);
                $setter->addParameter('value');
                $setter->addBody($body);
            }

            if (($body = $this->getGetterBody())) {
                $getter = $class->addMethod(Str::methodName($name . ' Attribute', 'get'));
                $getter->setVisibility(ClassType::VISIBILITY_PROTECTED);
                $getter->addBody($body);
            }
        } else {
            if (($body = $this->getSetterBody())) {
                $setter = $class->addMethod(Str::methodName($name . ' Attribute', 'set'));
                $setter->setVisibility(ClassType::VISIBILITY_PUBLIC);
                $setter->addParameter('value');
                $setter->addBody($body);
            }

            if (($body = $this->getGetterBody())) {
                $getter = $class->addMethod(Str::methodName($name . ' Attribute', 'get'));
                $getter->setVisibility(ClassType::VISIBILITY_PUBLIC);
                $getter->addBody($body);
                $getter->setReturnType($type);
            }
        }

        return parent::observeBaseEntityClass($class);
    }

}
