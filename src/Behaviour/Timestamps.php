<?php
declare(strict_types=1);

namespace Sirius\Orm\Behaviour;

use Sirius\Orm\Action\ActionInterface;
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

    public function onSave(/** @scrutinizer ignore-unused */Mapper $mapper, ActionInterface $action)
    {
        if ($action instanceof Insert) {
            if ($this->createColumn) {
                $action->addColumns([$this->createColumn => time()]);
            }
            if ($this->updateColumn) {
                $action->addColumns([$this->updateColumn => time()]);
            }
        }
        if ($action instanceof Update && $this->updateColumn) {
            if (! empty($action->getEntity()->getChanges())) {
                $action->addColumns([$this->updateColumn => time()]);
            }
        }

        return $action;
    }
}
