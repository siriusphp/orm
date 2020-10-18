<?php
declare(strict_types=1);

namespace Sirius\Orm\Definition\Behaviour;

use Nette\PhpGenerator\ClassType;
use Sirius\Orm\Definition\Behaviour;
use Sirius\Orm\Definition\Column;
use Sirius\Orm\Definition\Mapper;
use Sirius\Orm\Query\SoftDeleteTrait;

class Timestamps extends Behaviour
{

    protected $createdAtColumn = 'created_at';

    protected $updatedAtColumn = 'updated_at';

    static public function make($createdAtColumn = 'created_at', $updatedAtColumn = 'updated_at')
    {
        return parent::make()
                     ->setCreatedAtColumn($createdAtColumn)
                     ->setUpdatedAtColumn($updatedAtColumn);
    }

    function getName(): string
    {
        return 'timestamps';
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

    public function observeBaseMapperClass(ClassType $class): ClassType
    {
        $class->addProperty('createdAtColumn', $this->createdAtColumn)
              ->setVisibility('protected');
        $class->addProperty('updatedAtColumn', $this->updatedAtColumn)
              ->setVisibility('protected');
        return parent::observeBaseMapperClass($class);
    }

    public function observeBaseQueryClass(ClassType $class): ClassType
    {
        $class->getNamespace()->addUse(\Sirius\Orm\Query\TimestampsTrait::class);
        $class->addTrait('TimestampsTrait');
        $class->addProperty('createdAtColumn', $this->createdAtColumn)
              ->setVisibility('protected');
        $class->addProperty('updatedAtColumn', $this->updatedAtColumn)
              ->setVisibility('protected');
        return parent::observeBaseQueryClass($class);
    }

}
