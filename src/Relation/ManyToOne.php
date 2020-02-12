<?php

namespace Sirius\Orm\Relation;

use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\Entity\LazyValueLoader;
use Sirius\Orm\Entity\Tracker;

class ManyToOne extends Relation
{
    protected function getDefaultOptions()
    {
        $defaults = parent::getDefaultOptions();

        $defaults[RelationOption::CASCADE] = false;

        $foreignKey                            = $this->foreignMapper->getPrimaryKey();
        $defaults[RelationOption::FOREIGN_KEY] = $foreignKey;

        $nativeKey                            = $this->getKeyColumn($this->name, $this->foreignMapper->getPrimaryKey());
        $defaults[RelationOption::NATIVE_KEY] = $nativeKey;

        return $defaults;
    }

    public function attachesMatchesToEntity(EntityInterface $nativeEntity, array $result)
    {
        $found = null;
        foreach ($result as $foreignEntity) {
            if ($this->entitiesBelongTogether($nativeEntity, $foreignEntity)) {
                $found = $foreignEntity;
                break;
            }
        }

        $nativeKey  = (array)$this->getOption(RelationOption::NATIVE_KEY);
        $foreignKey = (array)$this->getOption(RelationOption::FOREIGN_KEY);

        foreach ($nativeKey as $k => $col) {
            $this->nativeMapper->setEntityAttribute(
                $nativeEntity,
                $col,
                $found ? $this->foreignMapper->getEntityAttribute($found, $foreignKey[$k]) : null
            );
        }

        $this->nativeMapper->setEntityAttribute($nativeEntity, $this->name, $found);
    }

    public function attachLazyValueToEntity(EntityInterface $entity, Tracker $tracker)
    {
        $valueLoader = new LazyValueLoader($entity, $tracker, $this);
        $this->nativeMapper->setEntityAttribute($entity, $this->name, $valueLoader);
    }
}
