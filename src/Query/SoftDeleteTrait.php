<?php
declare(strict_types=1);

namespace Sirius\Orm\Query;

trait SoftDeleteTrait
{
    public function withTrashed()
    {
        $guards = [];
        foreach ($this->guards as $k => $v) {
            if ($v != $this->deletedAtColumn . ' IS NULL') {
                $guards[$k] = $v;
            }
        }
        $this->guards = $guards;

        return $this;
    }

    public function withoutTrashed()
    {
        $guards = [];
        $hasGuard = false;
        foreach ($this->guards as $k => $v) {
            if ($v === $this->deletedAtColumn . ' IS NULL') {
                $hasGuard = true;
                break;
            }
        }

        if (!$hasGuard) {
            $this->guards[] = $this->deletedAtColumn . ' IS NULL';
        }

        return $this;
    }

    public function onlyTrashed()
    {
        $this->withTrashed();
        $this->guards[] = $this->deletedAtColumn . ' IS NOT NULL';

        return $this;
    }
}
