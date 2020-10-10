<?php
declare(strict_types=1);

namespace Sirius\Orm;

use Sirius\Orm\Behaviour\BehaviourInterface;
use Sirius\Orm\Helpers\Str;

class Behaviours
{

    protected $list = [];

    public function add(BehaviourInterface $behaviour)
    {
        $this->list[$behaviour->getName()] = $behaviour;
    }

    public function remove($name)
    {
        unset($this->list[$name]);
    }

    public function without(...$names)
    {
        $clone = clone $this;
        foreach ($names as $name) {
            $clone->remove($name);
        }

        return $clone;
    }

    public function apply($mapper, $target, $result, ...$args)
    {
        foreach ($this->list as $behaviour) {
            $method = 'on' . Str::className($target);
            if (method_exists($behaviour, $method)) {
                $result = $behaviour->{$method}($mapper, $result, ...$args);
            }
        }

        return $result;
    }
}
