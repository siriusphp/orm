<?php

namespace Sirius\Orm;

class QueryBuilder
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
     * @var
     */
    protected $select;

    public function __construct(Orm $orm, Mapper $mapper)
    {
        $this->orm    = $orm;
        $this->mapper = $mapper;
    }

    public function newQuery($view = 'default')
    {
        $queryClass = $this->getQueryClass($this->mapper);

        return new $queryClass($this->orm, $this->mapper, $this->orm->getConnectionLocator()->getRead());
    }

    protected function getQueryClass(Mapper $mapper)
    {
        $queryClass = get_class($mapper) . 'Query';
        if (class_exists($queryClass)) {
            return $queryClass;
        }

        return Query::class;
    }
}
