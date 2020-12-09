<?php
declare(strict_types=1);

namespace Sirius\Orm\Event;

use League\Event\HasEventName;
use Sirius\Orm\Query;

class NewQuery implements HasEventName
{

    /**
     * @var string
     */
    private $mapper;

    /**
     * @var Query
     */
    private $query;

    public function __construct(string $mapper, Query $query)
    {
        $this->mapper = $mapper;
        $this->query = $query;
    }

    public function getQuery(): Query
    {
        return $this->query;
    }

    public function setQuery(Query $query)
    {
        $this->query = $query;
    }

    public function eventName(): string
    {
        return $this->mapper . '.query';
    }
}
