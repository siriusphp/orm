<?php
declare(strict_types=1);

namespace Sirius\Orm\Contract;

interface HydratorInterface
{
    public function hydrate(array $attributes = []);

    public function extract(EntityInterface $entity);

    public function get(EntityInterface $entity, $attribute);

    public function set(EntityInterface $entity, $attribute, $value);

    public function getPk(EntityInterface $entity);

    public function setPk(EntityInterface $entity, $value);
}
