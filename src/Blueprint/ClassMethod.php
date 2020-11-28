<?php
declare(strict_types=1);

namespace Sirius\Orm\Blueprint;

use Nette\PhpGenerator\ClassType;

/**
 * This class is used to register methods for various classes
 * (entity, mapper, query)
 */
class ClassMethod extends Base
{
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
            $errors[] = 'Method requires a name';
        }

        if ( ! in_array($this->visibility, [ClassType::VISIBILITY_PUBLIC, ClassType::VISIBILITY_PROTECTED])) {
            $errors[] = 'Wrong method visilibity type. Only `public` and `protected` are allowed.';
        }

        return $errors;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ClassMethod
    {
        $this->name = $name;

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function addParameter(string $name, string $type = null, $default = null)
    {
        $this->parameters[$name] = ['type' => $type, $default => $default];

        return $this;
    }

    public function getReturnType(): ?string
    {
        return $this->returnType;
    }

    public function setReturnType(string $returnType = null): ClassMethod
    {
        $this->returnType = $returnType;

        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): ClassMethod
    {
        $this->body = $body;

        return $this;
    }


    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): ClassMethod
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getComments(): ?string
    {
        return $this->comments;
    }

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
