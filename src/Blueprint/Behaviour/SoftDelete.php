<?php
declare(strict_types=1);

namespace Sirius\Orm\Blueprint\Behaviour;

use Sirius\Orm\Blueprint\Behaviour;
use Sirius\Orm\Blueprint\Column;
use Sirius\Orm\Blueprint\Mapper;
use Sirius\Orm\Blueprint\MapperAwareTrait;
use Sirius\Orm\CodeGenerator\Observer\Behaviour\SoftDeleteObserver;

class SoftDelete extends Behaviour
{
    use MapperAwareTrait;

    protected $deletedAtColumn = 'deleted_at';

    /**
     * @var SoftDeleteObserver
     */
    protected $observer;

    public static function make($deletedAtColumn = 'deleted_at')
    {
        return (new static)->setDeletedAtColumn($deletedAtColumn);
    }

    public function getName(): string
    {
        return 'soft_delete';
    }

    public function getObservers(): array
    {
        $observer = $this->getObserver()->with($this);

        return [
            $this->mapper->getName() . '_base_mapper' => [$observer],
            $this->mapper->getName() . '_base_query'  => [$observer]
        ];
    }

    /**
     * @return string
     */
    public function getDeletedAtColumn(): string
    {
        return $this->deletedAtColumn;
    }

    /**
     * @param string $deletedAtColumn
     *
     * @return SoftDelete
     */
    public function setDeletedAtColumn(string $deletedAtColumn): SoftDelete
    {
        $this->deletedAtColumn = $deletedAtColumn;

        return $this;
    }

    public function setMapper(Mapper $mapper): self
    {
        $this->mapper = $mapper;

        $columns = $mapper->getColumns();

        if ($this->deletedAtColumn && ! array_key_exists($this->deletedAtColumn, $columns)) {
            $mapper->addColumn(Column::datetime($this->deletedAtColumn)
                                     ->setNullable(true));
        }

        return $this;
    }

    /**
     * @return SoftDeleteObserver
     */
    public function getObserver(): SoftDeleteObserver
    {
        return $this->observer ?? new SoftDeleteObserver();
    }

    /**
     * @param SoftDeleteObserver $observer
     *
     * @return SoftDelete
     */
    public function setObserver(SoftDeleteObserver $observer): SoftDelete
    {
        $this->observer = $observer;

        return $this;
    }
}
