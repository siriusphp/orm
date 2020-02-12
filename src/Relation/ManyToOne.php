<?php

namespace Sirius\Orm\Relation;

use Sirius\Orm\Action\BaseAction;
use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Entity\EntityInterface;

class ManyToOne extends Relation
{
    protected function applyDefaults(): void
    {
        parent::applyDefaults();

        $foreignKey = $this->foreignMapper->getPrimaryKey();
        if (! isset($this->options[RelationOption::FOREIGN_KEY])) {
            $this->options[RelationOption::FOREIGN_KEY] = $foreignKey;
        }

        if (! isset($this->options[RelationOption::NATIVE_KEY])) {
            $nativeKey                                 = $this->getKeyColumn($this->name, $foreignKey);
            $this->options[RelationOption::NATIVE_KEY] = $nativeKey;
        }
    }

    public function attachMatchesToEntity(EntityInterface $nativeEntity, array $result)
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

    protected function attachToDelete(BaseAction $action)
    {
        return;
    }

    protected function attachToSave(BaseAction $action)
    {
        return;
    }
}
