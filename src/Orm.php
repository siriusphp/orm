<?php
declare(strict_types=1);

namespace Sirius\Orm;

class Orm implements MapperLocator
{
    protected $mappers = [];

    protected $lazyMappers = [];

    /**
     * @var ConnectionLocator
     */
    protected $connectionLocator;

    public function __construct(ConnectionLocator $connectionLocator)
    {
        $this->connectionLocator = $connectionLocator;
    }

    public function register($mapperName, $mapperOrConfigOrFactory): self
    {
        if ($mapperOrConfigOrFactory instanceof MapperConfig || is_callable($mapperOrConfigOrFactory)) {
            $this->lazyMappers[$mapperName] = $mapperOrConfigOrFactory;
        } elseif ($mapperOrConfigOrFactory instanceof Mapper) {
            $this->mappers[$mapperName] = $mapperOrConfigOrFactory;
        } else {
            throw new \InvalidArgumentException('$mapperOrConfigOrFactory must be a Mapper instance, 
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
            unset($this->lazyMappers[$mapperName]);
        }

        if (! isset($this->mappers[$mapperName]) || ! $this->mappers[$mapperName]) {
            throw new \InvalidArgumentException(sprintf('Mapper named %s is not registered', $mapperName));
        }

        return $this->mappers[$mapperName];
    }

    private function buildMapper($mapperConfigOrFactory): Mapper
    {
        if ($mapperConfigOrFactory instanceof MapperConfig) {
            return Mapper::make($this, $mapperConfigOrFactory);
        }

        $mapper = $mapperConfigOrFactory($this);
        if (! $mapper instanceof Mapper) {
            throw new \InvalidArgumentException(
                'The mapper generated from the factory is not a valid `Mapper` instance'
            );
        }

        return $mapper;
    }

    public function getConnectionLocator(): ConnectionLocator
    {
        return $this->connectionLocator;
    }
}
