<?php
declare(strict_types=1);

namespace Sirius\Orm\Contract;

interface ActionInterface
{
    /**
     * Called by external objects
     * Can executed other actions that are registered before/after
     * Should be written inside a try/catch block
     *
     * @see BaseAction::run()
     *
     * @return mixed
     */
    public function run();

    /**
     * Executed if the run() method is successful
     * Should not include code that is likely to throw exceptions
     *
     * @see Update::onSuccess() Sets `syncronized` as state of the updated entity
     *
     * @return mixed
     */
    public function onSuccess();
}
