<?php
declare(strict_types=1);

namespace Sirius\Orm\Action;

use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\Mapper;
use Sirius\Orm\Orm;
use Sirius\Orm\Relation\Relation;

abstract class BaseAction implements ActionInterface
{
    /**
     * @var Orm
     */
    protected $orm;

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

    public function __construct(
        Orm $orm,
        Mapper $mapper,
        EntityInterface $entity,
        EntityInterface $parentEntity = null,
        Relation $relation = null
    ) {
        $this->orm          = $orm;
        $this->mapper       = $mapper;
        $this->entity       = $entity;
        $this->parentEntity = $parentEntity;
        $this->relation     = $relation;
    }

    public function prepend(ActionInterface $action)
    {
        $this->before[] = $action;
    }

    public function append(ActionInterface $action)
    {
        $this->after[] = $action;
    }

    protected function attachActionsForRelatedEntities()
    {
        /**
         * @todo
         */
    }

    public function run()
    {
        $this->attachActionsForRelatedEntities();
        $executed = [];
        try {
            foreach ($this->before as $action) {
                $action->run();
                $executed[] = $action;
            }
            $this->execute();
            $executed[]   = $this;
            $this->hasRun = true;
            foreach ($this->after as $action) {
                $action->run();
                $executed[] = $action;
            }
        } catch (\Exception $e) {
            $this->undo($executed);
            throw $e;
        }

        return true;
    }

    public function revert()
    {
        throw new \BadMethodCallException(sprintf('%s must implement `revert()`', get_class($this)));
    }

    protected function undo(array $executed)
    {
        foreach ($executed as $action) {
            $action->revert();
        }
    }


    protected function execute()
    {
        throw new \BadMethodCallException(sprintf('%s must implement `execute()`', get_class($this)));
    }
}
