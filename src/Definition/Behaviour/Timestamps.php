<?php
declare(strict_types=1);

namespace Sirius\Orm\Definition\Behaviour;

use Nette\PhpGenerator\ClassType;
use Sirius\Orm\Definition\Behaviour;
use Sirius\Orm\Definition\Column;
use Sirius\Orm\Definition\Mapper;

class Timestamps extends Behaviour
{

    protected $createdAtColumn = 'created_at';

    protected $updatedAtColumn = 'updated_at';

    static public function make($createdAtColumn = 'created_at', $updatedAtColumn = 'updated_at')
    {
        return (new static)
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

    public function observeBaseQueryClass(ClassType $class): ClassType
    {
        $class->addProperty('createdAtColumn', $this->createdAtColumn)
              ->setVisibility('protected');
        $class->addProperty('updatedAtColumn', $this->updatedAtColumn)
              ->setVisibility('protected');

        // add methods
        $class->addMethod('orderByFirstCreated')
              ->setVisibility('public')
              ->setBody('
$this->orderBy($this->createdAtColumn . \' ASC\');

return $this;            
            ');
        $class->addMethod('orderByLastCreated')
              ->setVisibility('public')
              ->setBody('
$this->orderBy($this->createdAtColumn . \' DESC\');

return $this;            
            ');
        $class->addMethod('orderByFirstUpdated')
              ->setVisibility('public')
              ->setBody('
$this->orderBy($this->updatedAtColumn . \' ASC\');

return $this;            
            ');
        $class->addMethod('orderByLastCreated')
              ->setVisibility('public')
              ->setBody('
$this->orderBy($this->updatedAtColumn . \' DESC\');

return $this;            
            ');

        return parent::observeBaseQueryClass($class);
    }

    public function observeBaseMapperClass(ClassType $class): ClassType
    {
        $class->addProperty('createdAtColumn', $this->createdAtColumn)->setVisibility('protected');
        $class->addProperty('updatedAtColumn', $this->updatedAtColumn)->setVisibility('protected');

        if ( ! $class->hasMethod('init')) {
            $class->addMethod('init')->setVisibility('public')
                  ->setBody('parent::init();' . PHP_EOL);
        }
        $class->getNamespace()->addUse(\Sirius\Orm\Behaviour\Timestamps::class);
        $method = $class->getMethod('init');
        $method->addBody(PHP_EOL . '$this->behaviours->add(new Timestamps($this->createdAtColumn, $this->updatedAtColumn));');

        return parent::observeBaseMapperClass($class);
    }

}
