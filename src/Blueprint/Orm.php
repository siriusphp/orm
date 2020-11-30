<?php
declare(strict_types=1);

namespace Sirius\Orm\Blueprint;

class Orm extends Base
{
    /**
     * @var string
     */
    protected $mapperNamespace;

    /**
     * @var string
     */
    protected $mapperDestination;

    /**
     * @var string
     */
    protected $entityNamespace;

    /**
     * @var string
     */
    protected $entityDestination;

    /**
     * @var array
     */
    protected $mappers = [];

    public static function make(): Orm
    {
        return new static;
    }

    public function getErrors(): array
    {
        $errors = [];

        if (! $this->entityNamespace) {
            $errors[] = 'Missing entity namespace property';
        }

        if (! $this->entityDestination) {
            $errors[] = 'Missing entity destination property';
        } elseif (! is_dir($this->entityDestination)) {
            $errors[] = sprintf('%s is not a valid directory', $this->entityDestination);
        } elseif (! is_writable($this->entityDestination)) {
            $errors[] = sprintf('%s is not writable', $this->entityDestination);
        }

        if (! $this->mapperNamespace) {
            $errors[] = 'Missing mapper namespace property';
        }

        if (! $this->mapperDestination) {
            $errors[] = 'Missing entity destination property';
        } elseif (! is_dir($this->mapperDestination)) {
            $errors[] = sprintf('%s is not a valid directory', $this->mapperDestination);
        } elseif (! is_writable($this->mapperDestination)) {
            $errors[] = sprintf('%s is not writable', $this->mapperDestination);
        }

        /** @var Mapper $mapper */
        foreach ($this->mappers as $name => $mapper) {
            foreach ($mapper->getErrors() as $error) {
                $errors[] = sprintf('Mapper %s: %s', $name, $error);
            }
        }

        return $errors;
    }

    public function getObservers(): array
    {
        $observers = [];
        /** @var Mapper $mapper */
        foreach ($this->mappers as $mapper) {
            $observers = array_merge_recursive($observers, $mapper->getObservers());
        }

        return $observers;
    }

    public function applyObservers(string $key, $object)
    {
        $observers = $this->getObservers()[$key] ?? [];
        /** @var \Sirius\Orm\CodeGenerator\Observer\Base $observer */
        foreach ($observers as $observer) {
            $object = $observer->observe($key, $object);
        }

        return $object;
    }

    public function getMapperNamespace(): string
    {
        return $this->mapperNamespace;
    }

    /**
     * Set the default namespace for future mappers
     */
    public function setMapperNamespace(string $mapperNamespace): Orm
    {
        $this->mapperNamespace = $mapperNamespace;

        return $this;
    }

    public function getMapperDestination(): string
    {
        return $this->mapperDestination;
    }

    /**
     * Set default destination for future mappers
     */
    public function setMapperDestination(string $mapperDestination): Orm
    {
        $this->mapperDestination = $mapperDestination;

        return $this;
    }

    public function getEntityNamespace(): string
    {
        return $this->entityNamespace;
    }

    /**
     * Set default namespace for the entity classes to be be generated
     */
    public function setEntityNamespace(string $entityNamespace): Orm
    {
        $this->entityNamespace = $entityNamespace;

        return $this;
    }

    public function getEntityDestination(): string
    {
        return $this->entityDestination;
    }

    /**
     * Set default destination for the entity classes to be be generated
     */
    public function setEntityDestination(string $entityDestination): Orm
    {
        $this->entityDestination = $entityDestination;

        return $this;
    }

    /**
     * @return array|Mapper[]
     */
    public function getMappers(): array
    {
        return $this->mappers;
    }

    public function getMapper($name): ?Mapper
    {
        return $this->mappers[$name] ?? null;
    }

    public function addMapper(Mapper $mapper): Orm
    {
        $mapper->setOrm($this);
        $this->mappers[$mapper->getName()] = $mapper;

        return $this;
    }
}
