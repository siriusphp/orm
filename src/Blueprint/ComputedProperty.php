<?php
declare(strict_types=1);

namespace Sirius\Orm\Blueprint;

use Sirius\Orm\CodeGenerator\Observer\ComputedPropertyObserver;

class ComputedProperty extends Base
{
    use MapperAwareTrait;

    protected $name;

    /**
     * Type of property (int|float|DateTime|Some\Other\Class)
     * @var string
     */
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
            $this->mapper->getName() . '_base_entity' => [$observer],
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ComputedProperty
    {
        $this->name = $name;

        return $this;
    }

    public function getSetterBody(): ?string
    {
        return $this->setterBody;
    }

    public function setSetterBody(string $setterBody): ComputedProperty
    {
        $this->setterBody = $setterBody;

        return $this;
    }

    public function getGetterBody(): string
    {
        return $this->getterBody;
    }

    public function setGetterBody(string $getterBody): ComputedProperty
    {
        $this->getterBody = $getterBody;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): ComputedProperty
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
