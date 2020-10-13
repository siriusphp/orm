<?php
declare(strict_types=1);

namespace Sirius\Orm\Query;

trait SoftDeleteTrait
{
    protected $deletedAtColumn = 'deleted_at';

    protected function initSoftDelete() {
        $this->guards[] = $this->deletedAtColumn . ' IS NULL';
    }

    public function withTrashed()
    {
        $guards = [];
        foreach ($this->guards as $k => $v) {
            if ($v == $this->deletedAtColumn . ' IS NULL') {
                $guards[$k] = $v;
            }
        }
        $this->guards = $guards;

        return $this;
    }
}
