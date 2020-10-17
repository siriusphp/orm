<?php
declare(strict_types=1);

namespace Sirius\Orm\Collection;

use Sirius\Orm\Contract\HydratorInterface;

class PaginatedCollection extends Collection
{
    protected $totalCount;
    protected $perPage;
    protected $currentPage;

    public function __construct(array $elements, int $totalCount, int $perPage, int $currentPage, HydratorInterface $hydrator, $primaryKey)
    {
        parent::__construct($elements, $hydrator, $primaryKey);
        $this->totalCount  = $totalCount;
        $this->perPage     = $perPage;
        $this->currentPage = $currentPage;
    }

    /**
     * @return mixed
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * @return mixed
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * @return mixed
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    public function getTotalPages()
    {
        if ($this->perPage < 1) {
            return 0;
        }

        return ceil($this->totalCount / $this->perPage);
    }

    public function getPageStart()
    {
        if ($this->totalCount < 1) {
            return 0;
        }

        return 1 + $this->perPage * ($this->currentPage - 1);
    }

    public function getPageEnd()
    {
        if ($this->totalCount < 1) {
            return 0;
        }

        return $this->getPageStart() + $this->count() - 1;
    }
}
