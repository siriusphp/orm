<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

interface EntityInterface
{
    public function getPersistenceState();

    public function setPersistenceState($state);

    public function getArrayCopy();

    public function getChanges();
}
