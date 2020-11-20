<?php
declare(strict_types=1);

namespace Sirius\Orm\Contract;

use Sirius\Orm\CastingManager;
use Sirius\Orm\MapperConfig;

interface HydratorInterface
{
    public function setMapperConfig(MapperConfig $mapperConfig);

    public function hydrate(array $attributes = []);

    public function extract(EntityInterface $entity);

    public function get(EntityInterface $entity, $attribute);

    public function set(EntityInterface $entity, $attribute, $value);

    public function setLazy(EntityInterface $entity, $attribute, LazyLoader $lazyLoader);

    public function getPk(EntityInterface $entity);

    public function setPk(EntityInterface $entity, $value);
}
