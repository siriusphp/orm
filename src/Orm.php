<?php
declare(strict_types=1);

namespace Sirius\Orm;

use InvalidArgumentException;
use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\Helpers\Str;
use Sirius\Orm\Relation\Relation;
use Sirius\Orm\Relation\RelationOption;

class Orm implements MapperLocator
{
    protected $mappers = [];

    protected $lazyMappers = [];

    /**
     * @var ConnectionLocator
     */
    protected $connectionLocator;

    /**
     * @var CastingManager
     */
    protected $castingManager;

    public function __construct(ConnectionLocator $connectionLocator, CastingManager $castingManager = null)
    {
        $this->connectionLocator = $connectionLocator;
        if (! $castingManager) {
            $castingManager = new CastingManager();
        }
        $this->castingManager = $castingManager;
    }

    public function register($mapperName, $mapperOrConfigOrFactory): self
    {
        if ($mapperOrConfigOrFactory instanceof MapperConfig || is_callable($mapperOrConfigOrFactory)) {
            $this->lazyMappers[$mapperName] = $mapperOrConfigOrFactory;
        } elseif ($mapperOrConfigOrFactory instanceof Mapper) {
            $this->mappers[$mapperName] = $mapperOrConfigOrFactory;
            $mapperOrConfigOrFactory->registerCasts($this->castingManager);
        } else {
            throw new InvalidArgumentException('$mapperOrConfigOrFactory must be a Mapper instance, 
            a MapperConfig instance or a callable that returns a Mapper instance');
        }

        return $this;
    }

    public function has($mapperName): bool
    {
        return isset($this->mappers[$mapperName]) || isset($this->lazyMappers[$mapperName]);
    }

    public function get($mapperName): Mapper
    {
        if (isset($this->lazyMappers[$mapperName])) {
            $this->mappers[$mapperName] = $this->buildMapper($this->lazyMappers[$mapperName]);
            $this->mappers[$mapperName]->registerCasts($this->castingManager);
            unset($this->lazyMappers[$mapperName]);
        }

        if (! isset($this->mappers[$mapperName]) || ! $this->mappers[$mapperName]) {
            throw new InvalidArgumentException(sprintf('Mapper named %s is not registered', $mapperName));
        }

        return $this->mappers[$mapperName];
    }

    public function save($mapperName, EntityInterface $entity, ...$params)
    {
        return $this->get($mapperName)->save($entity, ...$params);
    }

    public function delete($mapperName, EntityInterface $entity, ...$params)
    {
        return $this->get($mapperName)->delete($entity, ...$params);
    }

    public function find($mapperName, EntityInterface $entity, ...$params)
    {
        return $this->get($mapperName)->find($entity, ...$params);
    }

    public function select($mapperName): Collection
    {
        return $this->get($mapperName)->newQuery();
    }

    public function createRelation(Mapper $nativeMapper, $name, $options): Relation
    {
        $foreignMapper = $options[RelationOption::FOREIGN_MAPPER];
        if ($this->has($foreignMapper)) {
            if (! $foreignMapper instanceof Mapper) {
                $foreignMapper = $this->get($foreignMapper);
            }
        }
        $type          = $options[RelationOption::TYPE];
        $relationClass = __NAMESPACE__ . '\\Relation\\' . Str::className($type);

        if (! class_exists($relationClass)) {
            throw new InvalidArgumentException("{$relationClass} does not exist");
        }

        return new $relationClass($name, $nativeMapper, $foreignMapper, $options);
    }

    private function buildMapper($mapperConfigOrFactory): Mapper
    {
        if ($mapperConfigOrFactory instanceof MapperConfig) {
            return Mapper::make($this, $mapperConfigOrFactory);
        }

        $mapper = $mapperConfigOrFactory($this);
        if (! $mapper instanceof Mapper) {
            throw new InvalidArgumentException(
                'The mapper generated from the factory is not a valid `Mapper` instance'
            );
        }

        return $mapper;
    }

    public function getConnectionLocator(): ConnectionLocator
    {
        return $this->connectionLocator;
    }

    /**
     * @return CastingManager
     */
    public function getCastingManager(): CastingManager
    {
        return $this->castingManager;
    }
}
