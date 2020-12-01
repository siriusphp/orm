<?php
declare(strict_types=1);

namespace Sirius\Orm\Contract;

use Sirius\Orm\Mapper;

interface HydratorInterface
{
    public function setMapper(Mapper $mapper);

    public function hydrate(array $attributes = []);

    public function hydrateToArray(array $attributes = []): array;

    public function extract(EntityInterface $entity);

    public function get(EntityInterface $entity, string $attribute);

    public function set(EntityInterface $entity, string $attribute, $value);

    public function getPk(EntityInterface $entity);

    public function setPk(EntityInterface $entity, $value);
}
