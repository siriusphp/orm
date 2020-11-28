<?php
declare(strict_types=1);

namespace Sirius\Orm\Blueprint;

use Nette\PhpGenerator\ClassType;

class ClassMethod extends Base
{
    use MapperAwareTrait;

    protected $name;

    protected $visibility = ClassType::VISIBILITY_PUBLIC;

    protected $parameters = [];

    protected $returnType = null;

    protected $body;

    protected $comments = '';

    public static function make(string $name = null)
    {
        return (new static)->setName($name);
    }

    public function getErrors(): array
    {
        $errors = [];

        if ( ! $this->name) {
            $errors[] = 'Column requires a name';
        }

        if (!in_array($this->visibility, [ClassType::VISIBILITY_PUBLIC, ClassType::VISIBILITY_PROTECTED])) {
            $errors[] = 'Wrong method visilibity type. Only `public` and `protected` are allowed.';
        }

        return $errors;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return ClassMethod
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

    public function addParameter(string $name, string $type = null, $default = null)
    {
        $this->parameters[$name] = ['type' => $type, $default => $default];
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReturnType()
    {
        return $this->returnType;
    }

    /**
     * @param string|null $returnType
     *
     * @return ClassMethod
     */
    public function setReturnType($returnType = null)
    {
        $this->returnType = $returnType;

        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     *
     * @return ClassMethod
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }



    /**
     * @return string
     */
    public function getVisibility(): string
    {
        return $this->visibility;
    }

    /**
     * @param string $visibility
     *
     * @return ClassMethod
     */
    public function setVisibility(string $visibility): ClassMethod
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * @return string
     */
    public function getComments(): string
    {
        return $this->comments;
    }

    /**
     * @param string $comments
     *
     * @return ClassMethod
     */
    public function setComments(string $comments): ClassMethod
    {
        $this->comments = $comments;

        return $this;
    }

    protected function addMethodToClass(ClassType $class)
    {
        $method = $class->addMethod($this->getName());

        if ($this->returnType) {
            $method->setReturnType($this->returnType);
        }

        if ($this->comments) {
            $method->addComment($this->comments);
        }

        if ($this->body) {
            $method->setBody($this->body);
        }

        foreach ($this->parameters as $name => $details) {
            $param = $method->addParameter($name, $details['default']);
            $param->setType($details['type']);
        }
    }

}
