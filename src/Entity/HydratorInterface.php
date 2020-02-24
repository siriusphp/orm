<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

use Sirius\Orm\Entity\Tracker;

interface HydratorInterface
{
    public function hydrate(array $attributes = []);

    public function extract(EntityInterface $entity);
}
