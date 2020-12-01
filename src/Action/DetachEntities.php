<?php
declare(strict_types=1);

namespace Sirius\Orm\Action;

class DetachEntities extends AttachEntities
{
    public function run()
    {
        /**
         * @todo store current attribute values
         */
    }

    public function onSuccess()
    {
        $this->relation->detachEntities($this->nativeEntity, $this->foreignEntity);
    }
}
