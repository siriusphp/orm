<?php
declare(strict_types=1);

namespace Sirius\Orm;

use InvalidArgumentException;
use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\Helpers\Str;
use Sirius\Orm\Relation\Relation;
use Sirius\Orm\Relation\RelationBuilder;
use Sirius\Orm\Relation\RelationConfig;

class Orm implements MapperLocator
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
     * @var RelationBuilder
     */
    protected $relationBuilder;

    public function __construct(
        ConnectionLocator $connectionLocator,
        CastingManager $castingManager = null
    ) {
        $this->connectionLocator = $connectionLocator;

        if (! $castingManager) {
            $castingManager = new CastingManager();
        }
        $this->castingManager = $castingManager;

        $this->relationBuilder = new RelationBuilder($this);
    }

    public function register($name, $mapperOrConfigOrFactory): self
    {
        if ($mapperOrConfigOrFactory instanceof MapperConfig || is_callable($mapperOrConfigOrFactory)) {
            $this->lazyMappers[$name] = $mapperOrConfigOrFactory;
        } elseif ($mapperOrConfigOrFactory instanceof Mapper) {
            $this->mappers[$name] = $mapperOrConfigOrFactory;
            $mapperOrConfigOrFactory->registerCasts($this->castingManager);
        } else {
            throw new InvalidArgumentException('$mapperOrConfigOrFactory must be a Mapper instance, 
            a MapperConfig instance or a callable that returns a Mapper instance');
        }

        return $this;
    }

    public function has($name): bool
    {
        return isset($this->mappers[$name]) || isset($this->lazyMappers[$name]);
    }

    public function get($name): Mapper
    {
        if (isset($this->lazyMappers[$name])) {
            $this->mappers[$name] = $this->buildMapper($this->lazyMappers[$name]);
            $this->mappers[$name]->registerCasts($this->castingManager);
            unset($this->lazyMappers[$name]);
        }

        if (! isset($this->mappers[$name]) || ! $this->mappers[$name]) {
            throw new InvalidArgumentException(sprintf('Mapper named %s is not registered', $name));
        }

        return $this->mappers[$name];
    }

    public function save($mapperName, EntityInterface $entity, $withRelations = true)
    {
        return $this->get($mapperName)->save($entity, $withRelations);
    }

    public function delete($mapperName, EntityInterface $entity, $withRelations = true)
    {
        return $this->get($mapperName)->delete($entity, $withRelations);
    }

    public function find($mapperName, $id, array $load = [])
    {
        return $this->get($mapperName)->find($id, $load);
    }

    public function select($mapperName): Query
    {
        return $this->get($mapperName)->newQuery();
    }

    public function createRelation(Mapper $nativeMapper, $name, $options): Relation
    {
        return $this->relationBuilder->newRelation($nativeMapper, $name, $options);
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
