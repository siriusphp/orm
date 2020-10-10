<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

interface HydratorInterface
{
    public function hydrate(array $attributes = []);

    public function extract(EntityInterface $entity);

    public function get($entity, $attribute);

    public function set($entity, $attribute, $value);

    public function getPk($entity);

    public function setPk($entity, $value);
}
