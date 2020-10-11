<?php
declare(strict_types=1);

namespace Sirius\Orm\Action;

use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\Mapper;
use Sirius\Orm\Relation\ManyToMany;
use Sirius\Orm\Relation\RelationConfig;

class DeletePivotRows extends BaseAction
{
    /**
     * @var Mapper
     */
    protected $nativeMapper;
    /**
     * @var EntityInterface
     */
    protected $nativeEntity;

    public function __construct(ManyToMany $relation, EntityInterface $nativeEntity, EntityInterface $foreignEntity)
    {
        $this->relation     = $relation;
        $this->nativeMapper = $relation->getNativeMapper();
        $this->nativeEntity = $nativeEntity;
        $this->mapper       = $relation->getForeignMapper();
        $this->entity       = $foreignEntity;
    }

    protected function execute()
    {
        $conditions = $this->getConditions();

        if (empty($conditions)) {
            return;
        }

        $delete = new \Sirius\Sql\Delete($this->mapper->getWriteConnection());
        $delete->from((string)$this->relation->getOption(RelationConfig::THROUGH_TABLE));
        $delete->whereAll($conditions, false);

        $delete->perform();
    }

    public function revert()
    {
        return; // no change to the entity has actually been performed
    }

    public function onSuccess()
    {
        return;
    }

    protected function getConditions()
    {
        $conditions = [];

        $nativeEntityPk    = (array)$this->nativeMapper->getConfig()->getPrimaryKey();
        $nativeThroughCols = (array)$this->relation->getOption(RelationConfig::THROUGH_NATIVE_COLUMN);
        foreach ($nativeEntityPk as $idx => $col) {
            $val = $this->getNativeEntityHydrator()->get($this->nativeEntity, $col);
            if ($val) {
                $conditions[$nativeThroughCols[$idx]] = $val;
            }
        }

        $entityPk    = (array)$this->mapper->getConfig()->getPrimaryKey();
        $throughCols = (array)$this->relation->getOption(RelationConfig::THROUGH_FOREIGN_COLUMN);
        foreach ($entityPk as $idx => $col) {
            $val = $this->getForeignEntityHydrator()->get($this->entity, $col);
            if ($val) {
                $conditions[$throughCols[$idx]] = $val;
            }
        }

        // not enough columns? bail
        if (count($conditions) != count($entityPk) + count($nativeEntityPk)) {
            return [];
        }

        return $conditions;
    }

    protected function getNativeEntityHydrator()
    {
        return $this->nativeMapper->getConfig()->getEntityHydrator();
    }

    protected function getForeignEntityHydrator()
    {
        return $this->mapper->getConfig()->getEntityHydrator();
    }
}
