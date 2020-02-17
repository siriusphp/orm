<?php

namespace Sirius\Orm;

class QueryBuilder
{
    /**
     * @var array
     */
    protected $queryClasses = [];

    /**
     * @var QueryBuilder
     */
    protected static $instance;

    public static function getInstance()
    {
        if (! static::$instance) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    public function newQuery(Mapper $mapper): Query
    {
        $queryClass = $this->getQueryClass($mapper);

        return new $queryClass($mapper, $mapper->getReadConnection());
    }

    protected function getQueryClass(Mapper $mapper)
    {
        $mapperClass = get_class($mapper);
        if (! isset($this->queryClasses[$mapperClass])) {
            $queryClass = $mapperClass . 'Query';
            if (class_exists($queryClass)) {
                $this->queryClasses[$mapperClass] = $queryClass;
            } else {
                $this->queryClasses[$mapperClass] = Query::class;
            }
        }

        return $this->queryClasses[$mapperClass];
    }
}
