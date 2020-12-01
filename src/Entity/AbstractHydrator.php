<?php
declare(strict_types=1);

namespace Sirius\Orm\Entity;

use Sirius\Orm\CastingManager;
use Sirius\Orm\Contract\EntityInterface;
use Sirius\Orm\Contract\HydratorInterface;
use Sirius\Orm\Contract\Relation\ToManyInterface;
use Sirius\Orm\Contract\Relation\ToOneInterface;
use Sirius\Orm\Helpers\Arr;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;

abstract class AbstractHydrator implements HydratorInterface
{
    /**
     * @var CastingManager
     */
    protected $castingManager;

    /**
     * @var Mapper
     */
    protected $mapper;

    public function __construct(CastingManager $castingManager)
    {
        $this->castingManager = $castingManager;
    }

    public function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * @param array $attributes
     *
     * @return mixed|GenericEntity
     */
    abstract public function hydrate(array $attributes = []);

    /**
     * @param EntityInterface $entity
     *
     * @return array
     */
    public function extract(EntityInterface $entity)
    {
        $data = Arr::renameKeys(
            $entity->toArray(),
            array_flip($this->getMapperConfig()->getColumnAttributeMap())
        );
        if ($this->castingManager) {
            $data = $this->castingManager
                ->castArrayForDb($data, $this->getMapperConfig()->getCasts());
        }

        return Arr::only($data, $this->getMapperConfig()->getColumns());
    }

    abstract public function get(EntityInterface $entity, string $attribute);

    abstract public function set(EntityInterface $entity, string $attribute, $value);

    public function getPk($entity)
    {
        return $this->get($entity, $this->getMapperConfig()->getPrimaryKey());
    }

    public function setPk($entity, $value)
    {
        return $this->set($entity, $this->getMapperConfig()->getPrimaryKey(), $value);
    }

    public function hydrateToArray(array $attributes= []): array
    {
        $attributes = Arr::renameKeys($attributes, $this->getMapperConfig()->getColumnAttributeMap());
        if ($this->castingManager) {
            $attributes = $this->castingManager
                ->castArray($attributes, $this->getMapperConfig()->getCasts());
        }

        return $this->compileRelations($attributes);
    }

    protected function compileRelations(array $attributes)
    {
        foreach ($this->mapper->getRelations() as $name) {
            $relation = $this->mapper->getRelation($name);
            if ($relation instanceof ToOneInterface
                && isset($attributes[$name])
                && ! is_object($attributes[$name])) {
                $attributes[$name] = $relation->getForeignMapper()->newEntity($attributes[$name]);
            } elseif ($relation instanceof ToManyInterface
                      && ! $relation instanceof ToOneInterface) {
                /**
                 * here we need to check against ToOneInterface as well
                 * because OneToOne relation extends
                 * OneToMany which implements ToOneInterface
                 * @todo remove this quirk
                 */
                if (isset($attributes[$name]) && is_array($attributes[$name])) {
                    $attributes[$name] = $relation->getForeignMapper()->newCollection($attributes[$name]);
                } else {
                    $attributes[$name] = new LazyValue($relation->getForeignMapper()->newCollection([]));
                }
            }
        }

        return $attributes;
    }

    protected function getMapperConfig(): MapperConfig
    {
        return $this->mapper->getConfig();
    }
}
