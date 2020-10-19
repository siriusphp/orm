<?php
declare(strict_types=1);

namespace Sirius\Orm\Query;

trait TimestampsTrait
{
    public function orderByFirstCreated()
    {
        $this->orderBy($this->createdAtColumn . ' ASC');
    }

    public function orderByLastCreated()
    {
        $this->orderBy($this->createdAtColumn . ' DESC');
    }

    public function orderByFirstUpdated()
    {
        $this->orderBy($this->updatedAtColumn . ' ASC');
    }

    public function orderByLastUpdated()
    {
        $this->orderBy($this->updatedAtColumn . ' DESC');
    }
}
