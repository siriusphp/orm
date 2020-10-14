<?php
declare(strict_types=1);

namespace Sirius\Orm;

use InvalidArgumentException;
use Sirius\Orm\Contract\CastingManagerAwareInterface;
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
     * Orm constructor.
     *
     * @param ConnectionLocator $connectionLocator
     */
    public function __construct(ConnectionLocator $connectionLocator = null, CastingManager $castingManager = null)
    {
        $this->connectionLocator = $connectionLocator;
        $this->castingManager    = $castingManager ?: new CastingManager();
    }

    /**
     * Register a mapper with a name
     *
     * @param string $name
     * @param Mapper|MapperConfig|callable $mapperOrConfigOrFactory
     *
     * @return $this
     */
    public function register(string $name, $mapperOrConfigOrFactory): self
    {
        if ($mapperOrConfigOrFactory instanceof MapperConfig || is_callable($mapperOrConfigOrFactory)) {
            $this->lazyMappers[$name] = $mapperOrConfigOrFactory;
        } elseif ($mapperOrConfigOrFactory instanceof Mapper) {
            $this->mappers[$name] = $mapperOrConfigOrFactory;
        } else {
            throw new InvalidArgumentException('$mapperOrConfigOrFactory must be a Mapper instance, 
            a MapperConfig instance or a callable that returns a Mapper instance');
        }

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

        if ( ! isset($this->mappers[$name]) || ! $this->mappers[$name]) {
            throw new InvalidArgumentException(sprintf('Mapper named %s is not registered', $name));
        }

        $mapper = $this->mappers[$name];
        $mapper->setOrm($this);

        return $mapper;
    }

    /**
     * @return CastingManager
     */
    public function getCastingManager(): CastingManager
    {
        return $this->castingManager;
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
            if ( ! $foreignMapper instanceof Mapper) {
                $foreignMapper = $this->get($foreignMapper);
            }
        }
        $type          = $options[RelationConfig::TYPE];
        $relationClass = 'Sirius\\Orm\\Relation\\' . Str::className($type);

        if ( ! class_exists($relationClass)) {
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
        if ($mapperConfigOrFactory instanceof Mapper) {
            return $mapperConfigOrFactory;
        }

        if ($mapperConfigOrFactory instanceof MapperConfig) {
            return Mapper::make($this->connectionLocator, $mapperConfigOrFactory);
        }

        $mapper = $mapperConfigOrFactory($this);
        if ( ! $mapper instanceof Mapper) {
            throw new InvalidArgumentException(
                'The mapper generated from the factory is not a valid `Mapper` instance'
            );
        }

        return $mapper;
    }
}
