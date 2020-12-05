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
        $delete->from((string)$this->relation->getOption(RelationConfig::PIVOT_TABLE));
        $delete->whereAll($conditions, false);

        $guards = $this->relation->getOption(RelationConfig::PIVOT_GUARDS);
        if ($guards) {
            foreach ($guards as $column => $value) {
                if (is_int($column)) {
                    $delete->where($value);
                } else {
                    $delete->where($column, $value);
                }
            }
        }

        $delete->perform();
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
        $pivotThroughCols = (array)$this->relation->getOption(RelationConfig::PIVOT_NATIVE_COLUMN);
        foreach ($nativeEntityPk as $idx => $col) {
            $val = $this->nativeEntityHydrator->get($this->nativeEntity, $col);
            if ($val) {
                $conditions[$pivotThroughCols[$idx]] = $val;
            }
        }

        $entityPk    = (array)$this->mapper->getConfig()->getPrimaryKey();
        $pivotColumns = (array)$this->relation->getOption(RelationConfig::PIVOT_FOREIGN_COLUMN);
        foreach ($entityPk as $idx => $col) {
            $val = $this->entityHydrator->get($this->entity, $col);
            if ($val) {
                $conditions[$pivotColumns[$idx]] = $val;
            }
        }

        // not enough columns? bail
        if (count($conditions) != count($entityPk) + count($nativeEntityPk)) {
            return [];
        }

        return $conditions;
    }
}
