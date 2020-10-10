<?php
declare(strict_types=1);

namespace Sirius\Orm\Action;

use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\Exception\FailedActionException;
use Sirius\Orm\Mapper;
use Sirius\Orm\Relation\Relation;

abstract class BaseAction implements ActionInterface
{
    /**
     * @var Mapper
     */
    protected $mapper;

    /**
     * @var EntityInterface
     */
    protected $entity;

    /**
     * Actions to be executed before the `execute()` method (save parent entities)
     * @var array
     */
    protected $before = [];

    /**
     * Actions to be executed after the `execute()` method (save child entities)
     * @var array
     */
    protected $after = [];

    protected $hasRun = false;

    /**
     * @var EntityInterface
     */
    protected $parentEntity;

    /**
     * @var Relation
     */
    protected $relation;

    /**
     * Contains additional options for the action:
     * - relations (relations that will be saved)
     *      - true: all related entities will be saved, without limit
     *      - false: do not save any related entity
     *      - array: list of the related entities to be saved
     *              (ex: ['category', 'category.parent', 'images'])
     * @var array
     */
    protected $options;

    public function __construct(
        Mapper $mapper,
        EntityInterface $entity,
        array $options = []
    ) {
        $this->mapper  = $mapper;
        $this->entity  = $entity;
        $this->options = $options;
    }

    public function prepend(ActionInterface $action)
    {
        $this->before[] = $action;
    }

    /**
     * @return EntityInterface
     */
    public function getEntity(): EntityInterface
    {
        return $this->entity;
    }

    public function getOption($name)
    {
        return $this->options[$name] ?? null;
    }

    public function append(ActionInterface $action)
    {
        $this->after[] = $action;
    }

    protected function addActionsForRelatedEntities()
    {
        if ($this->getOption('relations') === false || ! $this->mapper) {
            return;
        }

        foreach ($this->mapper->getRelations() as $name) {
            if ( ! $this->mapper->hasRelation($name)) {
                continue;
            }
            $this->mapper->getRelation($name)->addActions($this);
        }
    }

    protected function getConditions()
    {
        $entityPk   = (array)$this->mapper->getConfig()->getPrimaryKey();
        $conditions = [];
        foreach ($entityPk as $col) {
            $val = $this->getEntityHydrator()->get($this->entity, $col);
            if ($val) {
                $conditions[$col] = $val;
            }
        }

        // not enough columns? reset
        if (count($conditions) != count($entityPk)) {
            return [];
        }

        return $conditions;
    }

    public function run($calledByAnotherAction = false)
    {
        $executed = [];

        try {
            $this->addActionsForRelatedEntities();

            foreach ($this->before as $action) {
                $action->run(true);
                $executed[] = $action;
            }
            $this->execute();
            $executed[]   = $this;
            $this->hasRun = true;
            foreach ($this->after as $action) {
                $action->run(true);
                $executed[] = $action;
            }
        } catch (\Exception $e) {
            $this->undo($executed);
            throw new FailedActionException(
                sprintf("%s failed for mapper %s", get_class($this), $this->mapper->getConfig()->getTableAlias(true)),
                (int)$e->getCode(),
                $e
            );
        }

        /** @var ActionInterface $action */
        foreach ($executed as $action) {
            // if called by another action, that action will call `onSuccess`
            if ( ! $calledByAnotherAction || $action !== $this) {
                $action->onSuccess();
            }
        }

        return true;
    }

    public function revert()
    {
        return; // each action implements it's own logic if necessary
    }

    protected function undo(array $executed)
    {
        foreach ($executed as $action) {
            $action->revert();
        }
    }

    public function onSuccess()
    {
        return;
    }


    protected function execute()
    {
        throw new \BadMethodCallException(sprintf('%s must implement `execute()`', get_class($this)));
    }

    protected function getEntityHydrator()
    {
        return $this->mapper->getConfig()->getEntityHydrator();
    }
}
