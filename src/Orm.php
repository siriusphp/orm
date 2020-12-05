<?php
declare(strict_types=1);

namespace Sirius\Orm;

use InvalidArgumentException;
use Sirius\Orm\Contract\MapperLocatorInterface;
use Sirius\Orm\Entity\CollectionCaster;
use Sirius\Orm\Entity\EntityCaster;
use Sirius\Orm\Relation\Relation;
use Sirius\Orm\Relation\RelationBuilder;

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
     * @var RelationBuilder
     */
    protected $relationBuilder;

    public function __construct(
        ConnectionLocator $connectionLocator,
        RelationBuilder $relationBuilder = null,
        CastingManager $castingManager = null,
        MapperLocatorInterface $mapperLocator = null
    ) {
        $this->connectionLocator = $connectionLocator;
        $this->relationBuilder   = $relationBuilder ?? new RelationBuilder();
        $this->castingManager    = $castingManager ?? new CastingManager();
        $this->mapperLocator     = $mapperLocator;
    }

    /**
     * Register a mapper with a name
     *
     * @param string $name
     * @param Mapper|callable|string $mapper
     */
    public function register(string $name, $mapper)
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

    public function getCastingManager(): CastingManager
    {
        return $this->castingManager;
    }

    public function getConnectionLocator(): ConnectionLocator
    {
        return $this->connectionLocator;
    }

    public function createRelation(Mapper $mapper, string $name, array $options): Relation
    {
        return $this->relationBuilder->build($this, $mapper, $name, $options);
    }

    protected function buildMapper($mapperConfigOrFactory): Mapper
    {
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
        $this->castingManager->register('entity_' . $name, new EntityCaster($this, $name));
        $this->castingManager->register('collection_of_' . $name, new CollectionCaster($this, $name));
    }
}
