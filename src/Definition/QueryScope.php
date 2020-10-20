<?php
declare(strict_types=1);

namespace Sirius\Orm\Definition;

class QueryScope extends Base
{
    use MapperAwareTrait;

    protected $name;

    protected $parameters = [];

    protected $methodBody;

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
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     *
     * @return QueryScope
     */
    public function setParameters(array $parameters): QueryScope
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMethodBody()
    {
        return $this->methodBody;
    }

    /**
     * @param mixed $methodBody
     *
     * @return QueryScope
     */
    public function setMethodBody($methodBody): QueryScope
    {
        $this->methodBody = $methodBody;

        return $this;
    }


}
