<?php
declare(strict_types=1);

namespace Sirius\Orm\Action;

use Sirius\Orm\Contract\ActionInterface;
use Sirius\Orm\Contract\EntityInterface;
use Sirius\Orm\Contract\HydratorInterface;
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
    protected $parentEntity;

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

    /**
     * @var bool
     */
    protected $hasRun = false;

    /**
     * @var Relation
     */
    protected $relation;

    /**
     * @var HydratorInterface
     */
    protected $entityHydrator;

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
        $this->mapper         = $mapper;
        $this->entity         = $entity;
        $this->options        = $options;
        $this->entityHydrator = $mapper->getHydrator();
    }

    /**
     * Adds an action to be ran/executed BEFORE this action's execute()
     *
     * @param ActionInterface $action
     */
    public function prepend(ActionInterface $action)
    {
        $this->before[] = $action;
    }

    /**
     * Adds an action to be ran/executed AFTER this action's execute()
     *
     * @param ActionInterface $action
     */
    public function append(ActionInterface $action)
    {
        $this->after[] = $action;
    }

    /**
     * @return EntityInterface
     */
    public function getEntity(): EntityInterface
    {
        return $this->entity;
    }

    /**
     * @param $name
     *
     * @return mixed|null
     */
    public function getOption($name)
    {
        return $this->options[$name] ?? null;
    }

    public function includesRelation($relationName)
    {
        $relations = (array) $this->getOption('relations');

        return $relations === true || in_array($relationName, $relations);
    }

    /**
     * Calls the relations and checks if they have to attach other actions
     * Usually used for deep save/delete
     */
    protected function addActionsForRelatedEntities()
    {
        if (! $this->mapper) {
            return;
        }

        foreach ($this->mapper->getRelations() as $name) {
            $this->mapper->getRelation($name)->addActions($this);
        }
    }

    /**
     * Returns the conditions for the query to be executed
     * Usually used by UPDATE/DELETE queries
     *
     * @return array
     */
    protected function getConditions()
    {
        $entityPk   = (array)$this->mapper->getConfig()->getPrimaryKey();
        $conditions = [];
        foreach ($entityPk as $col) {
            $val = $this->entityHydrator->get($this->entity, $col);
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

    /**
     * Performs the action.
     * Runs the prepended actions, executes the main logic, runs the appended actions.
     *
     * @param false $calledByAnotherAction
     *
     * @return bool|mixed
     */
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
            throw new FailedActionException(
                sprintf("%s failed for mapper %s", get_class($this), $this->mapper->getConfig()->getTableAlias(true)),
                (int)$e->getCode(),
                $e
            );
        }

        // if called by another action, that action will call `onSuccess`
        if ($calledByAnotherAction) {
            return true;
        }

        /** @var ActionInterface $action */
        foreach ($executed as $action) {
            $action->onSuccess();
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function onSuccess()
    {
        return;
    }

    /**
     * Contains the code for the main purpose of the action
     * Eg: inserting/deleting a row, updating some fields etc
     */
    protected function execute()
    {
        throw new \BadMethodCallException(sprintf('%s must implement `execute()`', get_class($this)));
    }
}
