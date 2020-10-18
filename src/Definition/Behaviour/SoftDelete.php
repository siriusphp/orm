<?php
declare(strict_types=1);

namespace Sirius\Orm\Definition\Behaviour;

use Nette\PhpGenerator\ClassType;
use Sirius\Orm\Definition\Behaviour;
use Sirius\Orm\Definition\Column;
use Sirius\Orm\Definition\Mapper;
use Sirius\Orm\Mapper\SoftDeleteTrait;

class SoftDelete extends Behaviour
{
    protected $deletedAtColumn = 'deleted_at';

    static function make($deletedAtColumn = 'deleted_at')
    {
        return parent::make()->setDeletedAtColumn($deletedAtColumn);
    }

    function getName(): string
    {
        return 'soft_delete';
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

    public function observeBaseMapperClass(ClassType $class): ClassType
    {
        $class->getNamespace()->addUse(SoftDeleteTrait::class);
        $class->addTrait('SoftDeleteTrait');
        $class->addProperty('deletedAtColumn', $this->deletedAtColumn)
              ->setVisibility('protected');
        return parent::observeBaseMapperClass($class);
    }

    public function observeBaseQueryClass(ClassType $class): ClassType
    {
        $class->getNamespace()->addUse(\Sirius\Orm\Query\SoftDeleteTrait::class);
        $class->addTrait('SoftDeleteTrait');
        $class->addProperty('deletedAtColumn', $this->deletedAtColumn)
              ->setVisibility('protected');
        return parent::observeBaseQueryClass($class);
    }
}
