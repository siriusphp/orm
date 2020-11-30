<?php
declare(strict_types=1);

namespace Sirius\Orm\Blueprint;

use Closure;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\Parameter;
use ReflectionFunction;
use Sirius\Orm\Helpers\Str;
use Sirius\Orm\Relation\RelationConfig;

abstract class Relation extends Base
{
    use MapperAwareTrait;

    protected $observer;

    protected $name;

    protected $type;

    protected $nativeKey;

    protected $foreignMapper;

    protected $foreignKey;

    protected $foreignGuards = [];

    protected $loadStrategy = RelationConfig::LOAD_LAZY;

    protected $queryCallback;

    /**
     * @param string $foreignMapper defaults to the relation's name
     *
     * @return Relation|static
     */
    public static function make($foreignMapper = '')
    {
        $mapper = new static;
        $mapper->setForeignMapper($foreignMapper);

        return $mapper;
    }

    public function getErrors(): array
    {
        $errors = [];

        if (! $this->name) {
            $errors[] = "Unknown relation name";
        }

        if (! $this->type) {
            $errors[] = "Unknown relation type";
        }

        if (! $this->nativeKey) {
            $errors[] = "Missing native key column";
        }

        if (! $this->foreignMapper) {
            $errors[] = "Missing foreign mapper name";
        }

        if (! $this->foreignKey) {
            $errors[] = "Missing foreign key";
        }

        $strategies = [RelationConfig::LOAD_LAZY, RelationConfig::LOAD_EAGER, RelationConfig::LOAD_NONE];
        if (! in_array($this->loadStrategy, $strategies)) {
            $errors[] = sprintf("Relation loading strategy is not valid (allowed values: %s)", implode(', ', $strategies));
        }

        return $errors;
    }

    public function getObservers(): array
    {
        $observer = $this->getObserver()->with($this);

        return [
            $this->getMapper()->getName() . '_base_entity' => [$observer]
        ];
    }

    public function getNativeKey(): string
    {
        return $this->nativeKey;
    }

    public function setNativeKey(string $nativeKey): Relation
    {
        $this->nativeKey = $nativeKey;

        return $this;
    }

    public function getForeignMapper(): string
    {
        return $this->foreignMapper;
    }

    public function setForeignMapper($foreignMapper): Relation
    {
        $this->foreignMapper = $foreignMapper;

        return $this;
    }

    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    public function setForeignKey($foreignKey): Relation
    {
        $this->foreignKey = $foreignKey;

        return $this;
    }

    public function getLoadStrategy(): string
    {
        return $this->loadStrategy;
    }

    public function setLoadStrategy(string $loadStrategy): Relation
    {
        $this->loadStrategy = $loadStrategy;

        return $this;
    }

    public function getForeignGuards(): array
    {
        return $this->foreignGuards;
    }

    public function setForeignGuards(array $foreignGuards): Relation
    {
        $this->foreignGuards = $foreignGuards;

        return $this;
    }

    public function getQueryCallback(): ?callable
    {
        return $this->queryCallback;
    }

    public function setQueryCallback(callable $queryCallback = null): Relation
    {
        $this->queryCallback = $queryCallback;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Relation
    {
        $this->name = $name;

        return $this;
    }

    public function getObserver(): \Sirius\Orm\CodeGenerator\Observer\Base
    {
        if ($this->observer) {
            return $this->observer;
        }

        $class = get_class($this);
        $observerClass = str_replace(
            '\\Blueprint\\',
            '\\CodeGenerator\\Observer\\',
            $class
        ) . 'Observer';

        return new $observerClass();
    }

    public function setObserver(\Sirius\Orm\CodeGenerator\Observer\Base $observer): Relation
    {
        $this->observer = $observer;

        return $this;
    }

    public function toArray()
    {
        $result = [];
        foreach (get_object_vars($this) as $prop => $value) {
            if (in_array($prop, ['mapper', 'name'])) {
                continue;
            }

            if ($value !== null &&
                $value !== 0 &&
                $value !== '' &&
                $value !== [] &&
                ! is_object($value)) {
                $result[Str::underscore($prop)] = $value;
            }
            if (is_object($value) && is_callable($value)) {
                /** @var Closure $value */
                $result[Str::underscore($prop)] = new Literal($this->getClosureDump($value));
            }
        }

        return $result;
    }

    protected function getClosureDump(Closure $c)
    {
        $closure = \Nette\PhpGenerator\Closure::from($c);
        $r       = new ReflectionFunction($c);
        $body    = '';
        $lines   = file($r->getFileName());
        for ($l = $r->getStartLine(); $l < $r->getEndLine(); $l++) {
            $body .= trim($lines[$l], " \t");
        }
        // strip everything after the last }
        $body = preg_replace('/\}[^\}]+$/', '', $body);
        $closure->setBody($body);

        $params = $closure->getParameters();
        /** @var Parameter $param */
        foreach ($params as $param) {
            if (strpos($param->getType(), '\\') !== false
                && strpos($param->getType(), '\\') !== 0) {
                $param->setType('\\' . $param->getType());
            }
        }

        return (string)$closure;
    }
}
