<?php
declare(strict_types=1);

namespace Sirius\Orm\Blueprint;

use Nette\PhpGenerator\ClassType;
use Sirius\Orm\CodeGenerator\Observer\ComputedPropertyObserver;
use Sirius\Orm\Helpers\Str;

class ComputedProperty extends Base
{
    use MapperAwareTrait;

    protected $name;

    protected $type;

    protected $setterBody;

    protected $getterBody;

    /**
     * @var ComputedPropertyObserver
     */
    protected $observer;

    static function make($name = '')
    {
        return (new static)->setName($name);
    }

    public function getObservers(): array
    {
        $observer = $this->getObserver()->with($this);

        return [
            $this->mapper->getName() . '_base_entity'   => [$observer],
        ];
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

    /**
     * @return ComputedPropertyObserver
     */
    public function getObserver(): ComputedPropertyObserver
    {
        return $this->observer ?? new ComputedPropertyObserver();
    }

    /**
     * @param ComputedPropertyObserver $observer
     *
     * @return ComputedProperty
     */
    public function setObserver(ComputedPropertyObserver $observer): ComputedProperty
    {
        $this->observer = $observer;

        return $this;
    }


}
