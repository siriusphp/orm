<?php

namespace Sirius\Orm\Relation;

use Sirius\Orm\Action\BaseAction;
use Sirius\Orm\Contract\EntityInterface;
use Sirius\Orm\Entity\StateEnum;
use Sirius\Orm\Query;

class ManyToOne extends Relation
{
    protected function applyDefaults(): void
    {
        parent::applyDefaults();

        $foreignKey = $this->foreignMapper->getConfig()->getPrimaryKey();
        if ( ! isset($this->options[RelationConfig::FOREIGN_KEY])) {
            $this->options[RelationConfig::FOREIGN_KEY] = $foreignKey;
        }

        if ( ! isset($this->options[RelationConfig::NATIVE_KEY])) {
            $nativeKey                                 = $this->getKeyColumn($this->name, $foreignKey);
            $this->options[RelationConfig::NATIVE_KEY] = $nativeKey;
        }
    }

    public function joinSubselect(Query $query, string $reference)
    {
        $subselect = $query->subSelectForJoinWith($this->foreignMapper)
                           ->as($reference);

        $subselect = $this->applyQueryCallback($subselect);

        $subselect = $this->applyForeignGuards($subselect);

        return $query->join('INNER', $subselect->getStatement(), $this->getJoinOnForSubselect());
    }

    public function attachMatchesToEntity(EntityInterface $nativeEntity, array $result)
    {
        // no point in linking entities if the native one is deleted
        if ($nativeEntity->getState() == StateEnum::DELETED) {
            return;
        }

        $nativeId = $this->getEntityId($this->nativeMapper, $nativeEntity, array_keys($this->keyPairs));

        $found = $result[$nativeId] ?? [];

        $this->attachEntities($nativeEntity, $found[0] ?? null);
    }

    /**
     * @param EntityInterface $nativeEntity
     * @param EntityInterface $foreignEntity
     */
    public function attachEntities(EntityInterface $nativeEntity, EntityInterface $foreignEntity = null): void
    {
        // no point in linking entities if the native one is deleted
        if ($nativeEntity->getState() == StateEnum::DELETED) {
            return;
        }

        $nativeKey  = (array)$this->getOption(RelationConfig::NATIVE_KEY);
        $foreignKey = (array)$this->getOption(RelationConfig::FOREIGN_KEY);

        foreach ($nativeKey as $k => $col) {
            $this->nativeEntityHydrator->set(
                $nativeEntity,
                $col,
                $foreignEntity ? $this->foreignEntityHydrator->get($foreignEntity, $foreignKey[$k]) : null
            );
        }

        $this->nativeEntityHydrator->set($nativeEntity, $this->name, $foreignEntity);
    }

    public function detachEntities(EntityInterface $nativeEntity, EntityInterface $foreignEntity)
    {
        if ($nativeEntity->getState() == StateEnum::DELETED) {
            return;
        }

        // required for DELETED entities that throw errors if they are changed
        $state = $foreignEntity->getState();
        $foreignEntity->setState(StateEnum::SYNCHRONIZED);

        $nativeKey = (array)$this->getOption(RelationConfig::NATIVE_KEY);

        foreach ($nativeKey as $k => $col) {
            $this->nativeEntityHydrator->set($nativeEntity, $col, null);
        }

        $this->nativeEntityHydrator->set($nativeEntity, $this->name, null);
        $foreignEntity->setState($state);
    }

    protected function addActionOnDelete(BaseAction $action)
    {
        $this->addActionOnSave($action);
    }

    protected function addActionOnSave(BaseAction $action)
    {
        if ( ! $this->relationWasChanged($action->getEntity())) {
            return;
        }

        if ( ! $action->includesRelation($this->name)) {
            return;
        }

        $foreignEntity = $this->nativeEntityHydrator->get($action->getEntity(), $this->name);
        if ($foreignEntity) {
            $remainingRelations = $this->getRemainingRelations($action->getOption('relations'));
            $saveAction         = $this->foreignMapper
                ->newSaveAction($foreignEntity, ['relations' => $remainingRelations]);
            $saveAction->addColumns($this->getExtraColumnsForAction());
            $action->prepend($saveAction);
            $action->prepend($this->newSyncAction($action->getEntity(), $foreignEntity, 'save'));
        }
    }
}
