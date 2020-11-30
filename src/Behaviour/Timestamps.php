<?php
declare(strict_types=1);

namespace Sirius\Orm\Behaviour;

use Sirius\Orm\Contract\ActionInterface;
use Sirius\Orm\Action\Insert;
use Sirius\Orm\Action\Update;
use Sirius\Orm\Mapper;

class Timestamps implements BehaviourInterface
{

    /**
     * @var string
     */
    protected $createColumn;
    /**
     * @var string
     */
    protected $updateColumn;

    public function __construct($createColumn = 'created_at', $updateColumn = 'updated_at')
    {
        $this->createColumn = $createColumn;
        $this->updateColumn = $updateColumn;
    }

    public function getName()
    {
        return 'timestamps';
    }

    public function onNewSaveAction(/** @scrutinizer ignore-unused */ Mapper $mapper, ActionInterface $action)
    {
        $now = date('Y-m-d H:i:s', time());
        if ($action instanceof Insert) {
            if ($this->createColumn) {
                $action->addColumns([$this->createColumn => $now]);
            }
            if ($this->updateColumn) {
                $action->addColumns([$this->updateColumn => $now]);
            }
            $mapper->getHydrator()->set($action->getEntity(), $this->createColumn, $now);
            $mapper->getHydrator()->set($action->getEntity(), $this->updateColumn, $now);
        }
        if ($action instanceof Update && $this->updateColumn) {
            if (! empty($action->getEntity()->getChanges())) {
                $action->addColumns([$this->updateColumn => $now]);
                $mapper->getHydrator()->set($action->getEntity(), $this->updateColumn, $now);
            }
        }

        return $action;
    }
}
