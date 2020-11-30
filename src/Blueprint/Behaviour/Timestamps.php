<?php
declare(strict_types=1);

namespace Sirius\Orm\Blueprint\Behaviour;

use Sirius\Orm\Blueprint\Behaviour;
use Sirius\Orm\Blueprint\Column;
use Sirius\Orm\Blueprint\Mapper;
use Sirius\Orm\Blueprint\MapperAwareTrait;
use Sirius\Orm\CodeGenerator\Observer\Behaviour\TimestampsObserver;

class Timestamps extends Behaviour
{
    use MapperAwareTrait;

    protected $createdAtColumn = 'created_at';

    protected $updatedAtColumn = 'updated_at';

    /**
     * @var TimestampsObserver
     */
    protected $observer;

    public static function make($createdAtColumn = 'created_at', $updatedAtColumn = 'updated_at')
    {
        return (new static)
            ->setCreatedAtColumn($createdAtColumn)
            ->setUpdatedAtColumn($updatedAtColumn);
    }

    public function getObservers(): array
    {
        $observer = $this->getObserver()->with($this);

        return [
            $this->mapper->getName() . '_base_mapper' => [$observer],
            $this->mapper->getName() . '_base_query'  => [$observer]
        ];
    }

    public function getName(): string
    {
        return 'timestamps';
    }

    /**
     * @return string
     */
    public function getCreatedAtColumn(): string
    {
        return $this->createdAtColumn;
    }

    /**
     * @param string $createdAtColumn
     *
     * @return Timestamps
     */
    public function setCreatedAtColumn(string $createdAtColumn): Timestamps
    {
        $this->createdAtColumn = $createdAtColumn;

        return $this;
    }

    /**
     * @return string
     */
    public function getUpdatedAtColumn(): string
    {
        return $this->updatedAtColumn;
    }

    /**
     * @param string $updatedAtColumn
     *
     * @return Timestamps
     */
    public function setUpdatedAtColumn(string $updatedAtColumn): Timestamps
    {
        $this->updatedAtColumn = $updatedAtColumn;

        return $this;
    }

    public function setMapper(Mapper $mapper): self
    {
        $this->mapper = $mapper;

        $columns = $mapper->getColumns();

        if ($this->createdAtColumn && ! array_key_exists($this->createdAtColumn, $columns)) {
            $mapper->addColumn(Column::datetime($this->createdAtColumn)
                                     ->setNullable(true));
        }

        if ($this->updatedAtColumn && ! array_key_exists($this->updatedAtColumn, $columns)) {
            $mapper->addColumn(Column::datetime($this->updatedAtColumn)
                                     ->setNullable(true)
                                     ->setAfter($this->createdAtColumn));
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getObserver()
    {
        return $this->observer ?? new TimestampsObserver();
    }

    /**
     * @param mixed $observer
     *
     * @return Timestamps
     */
    public function setObserver($observer)
    {
        $this->observer = $observer;

        return $this;
    }
}
