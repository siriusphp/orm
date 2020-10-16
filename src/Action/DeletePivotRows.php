<?php
declare(strict_types=1);

namespace Sirius\Orm\Action;

use Sirius\Orm\Contract\EntityInterface;
use Sirius\Orm\Contract\HydratorInterface;
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

    /**
     * @var HydratorInterface
     */
    protected $nativeEntityHydrator;

    public function __construct(ManyToMany $relation, EntityInterface $nativeEntity, EntityInterface $foreignEntity)
    {
        $this->relation = $relation;

        $this->nativeMapper         = $relation->getNativeMapper();
        $this->nativeEntity         = $nativeEntity;
        $this->nativeEntityHydrator = $this->nativeMapper->getHydrator();

        $this->mapper         = $relation->getForeignMapper();
        $this->entity         = $foreignEntity;
        $this->entityHydrator = $this->mapper->getHydrator();
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
        // no change to the entity has to be performed
        return;
    }

    public function onSuccess()
    {
        return;
    }

    /**
     * Computes the conditions for the DELETE statement that will
     * remove the linked rows from the PIVOT table for a many-to-many relation
     * @return array
     */
    protected function getConditions()
    {
        $conditions = [];

        $nativeEntityPk    = (array)$this->nativeMapper->getConfig()->getPrimaryKey();
        $nativeThroughCols = (array)$this->relation->getOption(RelationConfig::THROUGH_NATIVE_COLUMN);
        foreach ($nativeEntityPk as $idx => $col) {
            $val = $this->nativeEntityHydrator->get($this->nativeEntity, $col);
            if ($val) {
                $conditions[$nativeThroughCols[$idx]] = $val;
            }
        }

        $entityPk    = (array)$this->mapper->getConfig()->getPrimaryKey();
        $throughCols = (array)$this->relation->getOption(RelationConfig::THROUGH_FOREIGN_COLUMN);
        foreach ($entityPk as $idx => $col) {
            $val = $this->entityHydrator->get($this->entity, $col);
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
}
