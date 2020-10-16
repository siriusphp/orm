<?php
declare(strict_types=1);

namespace Sirius\Orm\Action;

use Sirius\Orm\Contract\ActionInterface;
use Sirius\Orm\Contract\EntityInterface;
use Sirius\Orm\Mapper;
use Sirius\Orm\Relation\ManyToMany;
use Sirius\Orm\Relation\Relation;
use Sirius\Orm\Relation\RelationConfig;

class AttachEntities implements ActionInterface
{
    /**
     * @var EntityInterface
     */
    protected $nativeEntity;
    /**
     * @var EntityInterface
     */
    protected $foreignEntity;
    /**
     * @var Relation
     */
    protected $relation;
    /**
     * @var string
     */
    protected $actionType;
    /**
     * @var Mapper
     */
    protected $nativeMapper;
    /**
     * @var Mapper
     */
    protected $foreignMapper;

    public function __construct(
        Mapper $nativeMapper,
        EntityInterface $nativeEntity,
        Mapper $foreignMapper,
        EntityInterface $foreignEntity,
        Relation $relation,
        string $actionType
    ) {
        $this->nativeMapper  = $nativeMapper;
        $this->nativeEntity  = $nativeEntity;
        $this->foreignMapper = $foreignMapper;
        $this->foreignEntity = $foreignEntity;
        $this->relation      = $relation;
        $this->actionType    = $actionType;
    }

    public function revert()
    {
        /**
         * @todo restore previous values
         */
    }

    public function run()
    {
        /**
         * @todo store current attribute values
         */
        $this->relation->attachEntities($this->nativeEntity, $this->foreignEntity);
        $this->maybeUpdatePivotRow();
    }

    public function onSuccess()
    {
    }

    protected function maybeUpdatePivotRow()
    {
        if ( ! $this->relation instanceof ManyToMany) {
            return;
        }

        $conn         = $this->relation->getNativeMapper()->getWriteConnection();
        $throughTable = (string)$this->relation->getOption(RelationConfig::THROUGH_TABLE);

        $throughNativeColumns  = (array)$this->relation->getOption(RelationConfig::THROUGH_NATIVE_COLUMN);
        $throughForeignColumns = (array)$this->relation->getOption(RelationConfig::THROUGH_FOREIGN_COLUMN);
        $nativeKey             = (array)$this->getNativeEntityHydrator()->getPk($this->nativeEntity);
        $foreignKey            = (array)$this->getForeignEntityHydrator()->getPk($this->foreignEntity);

        // first delete existing pivot row
        $delete = new \Sirius\Sql\Delete($conn);
        $delete->from($throughTable);
        foreach ($throughNativeColumns as $k => $col) {
            $delete->where($col, $nativeKey[$k]);
            $delete->where($throughForeignColumns[$k], $foreignKey[$k]);
        }
        foreach ((array)$this->relation->getOption(RelationConfig::THROUGH_GUARDS) as $col => $value) {
            if ( ! is_int($col)) {
                $delete->where($col, $value);
            } else {
                $delete->where($value);
            }
        }
        $delete->perform();

        $insertColumns = [];
        foreach ($throughNativeColumns as $k => $col) {
            $insertColumns[$col]                       = $nativeKey[$k];
            $insertColumns[$throughForeignColumns[$k]] = $foreignKey[$k];
        }

        $throughColumnPrefix = $this->relation->getOption(RelationConfig::THROUGH_COLUMNS_PREFIX);
        foreach ((array)$this->relation->getOption(RelationConfig::THROUGH_COLUMNS) as $col) {
            $insertColumns[$col] = $this->getForeignEntityHydrator()
                                        ->get($this->foreignEntity, "{$throughColumnPrefix}{$col}");
        }

        foreach ((array)$this->relation->getOption(RelationConfig::THROUGH_GUARDS) as $col => $value) {
            if ( ! is_int($col)) {
                $insertColumns[$col] = $value;
            }
        }

        $insert = new \Sirius\Sql\Insert($conn);
        $insert->into($throughTable)
               ->columns($insertColumns)
               ->perform();
    }

    protected function getNativeEntityHydrator()
    {
        return $this->nativeMapper->getHydrator();
    }

    protected function getForeignEntityHydrator()
    {
        return $this->foreignMapper->getHydrator();
    }
}
