<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

interface EntityInterface
{
    public function getPk();

    public function setPk($val);

    public function set($attribute, $value);

    public function get($attribute);

    public function getPersistanceState();

    public function setPersistanceState($state);

    public function getArrayCopy();

    public function getChanges();
}
