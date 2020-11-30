<?php
declare(strict_types=1);

namespace Sirius\Orm;

use InvalidArgumentException;
use Sirius\Orm\Contract\MapperLocatorInterface;
use Sirius\Orm\Entity\CollectionCaster;
use Sirius\Orm\Entity\EntityCaster;
use Sirius\Orm\Helpers\Str;
use Sirius\Orm\Relation\Relation;
use Sirius\Orm\Relation\RelationConfig;

class Orm
{
    /**
     * @var array
     */
    protected $mappers = [];

    /**
     * @var array
     */
    protected $lazyMappers = [];

    /**
     * @var ConnectionLocator
     */
    protected $connectionLocator;

    /**
     * @var CastingManager
     */
    protected $castingManager;

    /**
     * @var MapperLocatorInterface|null
     */
    protected $mapperLocator;

    /**
     * Orm constructor.
     *
     * @param ConnectionLocator $connectionLocator
     * @param CastingManager|null $castingManager
     * @param MapperLocatorInterface|null $mapperLocator
     */
    public function __construct(
        ConnectionLocator $connectionLocator,
        CastingManager $castingManager = null,
        MapperLocatorInterface $mapperLocator = null
    ) {
        $this->connectionLocator = $connectionLocator;
        $this->castingManager    = $castingManager ?: new CastingManager();
        $this->mapperLocator     = $mapperLocator;
    }

    /**
     * Register a mapper with a name
     *
     * @param string $name
     * @param Mapper|MapperConfig|callable $mapper
     *
     * @return $this
     */
    public function register(string $name, $mapper): self
    {
        if ($mapper instanceof Mapper) {
            $this->mappers[$name] = $mapper;
        } elseif (is_callable($mapper) || is_string($mapper) || $mapper instanceof MapperConfig) {
            $this->lazyMappers[$name] = $mapper;
        } else {
            throw new \InvalidArgumentException('The $mapper argument must be a Mapper object, 
                a MapperConfig object, a callable or a string that can be used by the mapper locator');
        }

        $this->addCastingMethodsForMapper($name);

        return $this;
    }

    /**
     * Check if a mapper is registered within the ORM
     *
     * @param $name
     *
     * @return bool
     */
    public function has($name): bool
    {
        return isset($this->mappers[$name]) || isset($this->lazyMappers[$name]);
    }

    /**
     * Return a mapper instance by it's registered name
     *
     * @param $name
     *
     * @return Mapper
     */
    public function get($name): Mapper
    {
        if (isset($this->lazyMappers[$name])) {
            $this->mappers[$name] = $this->buildMapper($this->lazyMappers[$name]);
            unset($this->lazyMappers[$name]);
        }

        if (! isset($this->mappers[$name]) || ! $this->mappers[$name]) {
            throw new InvalidArgumentException(sprintf('Mapper named %s is not registered', $name));
        }

        return $this->mappers[$name];
    }

    /**
     * @return CastingManager
     */
    public function getCastingManager(): CastingManager
    {
        return $this->castingManager;
    }

    public function getConnectionLocator(): ConnectionLocator
    {
        return $this->connectionLocator;
    }

    /**
     * Create a relation instance for a mapper based on t
     *
     * @param Mapper $mapper
     * @param string $name Name of the relation inside the mapper
     * @param array $options Array of options for the relation
     *
     * @return Relation
     */
    public function createRelation(Mapper $mapper, string $name, array $options): Relation
    {
        $foreignMapper = $options[RelationConfig::FOREIGN_MAPPER];
        if ($this->has($foreignMapper)) {
            if (! $foreignMapper instanceof Mapper) {
                $foreignMapper = $this->get($foreignMapper);
            }
        }
        $type          = $options[RelationConfig::TYPE];
        $relationClass = 'Sirius\\Orm\\Relation\\' . Str::className($type);

        if (! class_exists($relationClass)) {
            throw new InvalidArgumentException("{$relationClass} does not exist");
        }

        return new $relationClass($name, $mapper, $foreignMapper, $options);
    }

    /**
     * Build a mapper from a config or a factory function
     *
     * @param MapperConfig|callable $mapperConfigOrFactory
     *
     * @return Mapper
     */
    protected function buildMapper($mapperConfigOrFactory): Mapper
    {
        if ($mapperConfigOrFactory instanceof MapperConfig) {
            return DynamicMapper::make($this, $mapperConfigOrFactory);
        }

        $mapper = null;
        if (is_callable($mapperConfigOrFactory)) {
            $mapper = $mapperConfigOrFactory($this);
        } elseif ($this->mapperLocator) {
            $mapper = $this->mapperLocator->get($mapperConfigOrFactory);
        }

        if (! $mapper) {
            throw new InvalidArgumentException(
                'The mapper could not be generated/retrieved.'
            );
        }

        if (! $mapper instanceof Mapper) {
            throw new InvalidArgumentException(
                'The mapper generated from the factory is not a valid `Mapper` instance.'
            );
        }

        return $mapper;
    }

    protected function addCastingMethodsForMapper(string $name)
    {
        $this->castingManager->register('entity_from_' . $name, new EntityCaster($this, $name));
        $this->castingManager->register('collection_of_' . $name, new CollectionCaster($this, $name));
    }
}
