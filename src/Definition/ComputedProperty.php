<?php
declare(strict_types=1);

namespace Sirius\Orm\Definition;

class ComputedProperty extends Base
{
    use MapperAwareTrait;

    protected $name;

    protected $type;

    protected $setterBody;

    protected $getterBody;

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

}
