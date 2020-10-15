<?php
declare(strict_types=1);

namespace Sirius\Orm\Contract;

interface EntityInterface
{
    public function getState();

    public function setState($state);

    public function setLazy($attribute, LazyLoader $lazyLoader);

    public function getChanges();

    public function toArray();
}
