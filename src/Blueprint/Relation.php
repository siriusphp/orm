<?php
declare(strict_types=1);

namespace Sirius\Orm\Blueprint;

use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\Parameter;
use Sirius\Orm\Helpers\Str;
use Sirius\Orm\Relation\RelationConfig;

abstract class Relation extends Base
{
    use MapperAwareTrait;

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

        if ( ! $this->type) {
            $errors[] = "Uknown relation type";
        }

        if ( ! $this->nativeKey) {
            $errors[] = "Missing native key column";
        }

        if ( ! $this->foreignMapper) {
            $errors[] = "Missing foreign mapper name";
        }

        if ( ! $this->foreignKey) {
            $errors[] = "Missing foreign key";
        }

        $strategies = [RelationConfig::LOAD_LAZY, RelationConfig::LOAD_EAGER, RelationConfig::LOAD_NONE];
        if ( ! in_array($this->loadStrategy, $strategies)) {
            $errors[] = sprintf("Relation loading strategy is not valid (allowed values: %s)", implode(', ', $strategies));
        }

        return $errors;
    }

    /**
     * @return mixed
     */
    public function getNativeKey()
    {
        return $this->nativeKey;
    }

    /**
     * @param mixed $nativeKey
     *
     * @return Relation
     */
    public function setNativeKey($nativeKey)
    {
        $this->nativeKey = $nativeKey;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getForeignMapper()
    {
        return $this->foreignMapper;
    }

    /**
     * @param mixed $foreignMapper
     *
     * @return Relation
     */
    public function setForeignMapper($foreignMapper)
    {
        $this->foreignMapper = $foreignMapper;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * @param mixed $foreignKey
     *
     * @return Relation
     */
    public function setForeignKey($foreignKey)
    {
        $this->foreignKey = $foreignKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getLoadStrategy(): string
    {
        return $this->loadStrategy;
    }

    /**
     * @param string $loadStrategy
     *
     * @return Relation
     */
    public function setLoadStrategy(string $loadStrategy): Relation
    {
        $this->loadStrategy = $loadStrategy;

        return $this;
    }

    /**
     * @return array
     */
    public function getForeignGuards(): array
    {
        return $this->foreignGuards;
    }

    /**
     * @param array $foreignGuards
     *
     * @return Relation
     */
    public function setForeignGuards(array $foreignGuards): Relation
    {
        $this->foreignGuards = $foreignGuards;

        return $this;
    }

    /**
     * @return callable|null
     */
    public function getQueryCallback()
    {
        return $this->queryCallback;
    }

    /**
     * @param callable|null $queryCallback
     *
     * @return Relation
     */
    public function setQueryCallback(callable $queryCallback = null)
    {
        $this->queryCallback = $queryCallback;

        return $this;
    }



    public function toArray() {
        $result = [];
        foreach(get_object_vars($this) as $prop => $value) {
            if (in_array($prop, ['mapper'])) {
                continue;
            }

            if ($value !== null &&
                $value !== 0 &&
                $value !== '' &&
                $value !== [] &&
                !is_object($value)) {
                $result[Str::underscore($prop)] = $value;
            }
            if (is_object($value) && is_callable($value)) {
                /** @var \Closure $value */
                $result[Str::underscore($prop)] = new Literal($this->getClosureDump($value));
            }
        }

        return $result;
    }

    protected function getClosureDump(\Closure $c) {
        $closure = \Nette\PhpGenerator\Closure::from($c);
        $r = new \ReflectionFunction($c);
        $body = '';
        $lines = file($r->getFileName());
        for($l = $r->getStartLine(); $l < $r->getEndLine(); $l++) {
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

        return (string) $closure;
    }
}
