<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

interface EntityInterface
{
    public function getState();

    public function setState($state);

    public function getArrayCopy();

    public function getChanges();
}
