<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests;

use Sirius\Orm\Connection;
use PHPUnit\Framework\TestCase;
use Sirius\Orm\ConnectionLocator;
use Sirius\Orm\Orm;
use Sirius\Sql\Insert;
use Sirius\Sql\Select;

class BaseTestCase extends TestCase
{
    /**
     * @var Orm
     */
    protected $orm;
    /**
     * @var Connection
     */
    protected $connection;
    /**
     * @var \Atlas\Pdo\ConnectionLocator|ConnectionLocator
     */
    protected $connectionLocator;

    public function setUp(): void
    {
        parent::setUp();

        if (getenv('DB_ENGINE') == 'mysql') {
            $connection = Connection::new('mysql:host=localhost;dbname=sirius_orm', 'root', '');
        } else {
            $connection = Connection::new('sqlite::memory:');
        }

        $this->connection = $connection;
        $connectionLocator = ConnectionLocator::new($this->connection);
        $this->connectionLocator = $connectionLocator;
        $this->orm         = new Orm($connectionLocator);
        $this->createTables(getenv('DB_ENGINE') ? getenv('DB_ENGINE') : 'generic');
        $this->loadMappers();
        $connectionLocator->logQueries();
    }

    public function createTables($fileName = 'generic')
    {
        foreach (include(__DIR__ . "/resources/tables/{$fileName}.php") as $sql) {
            $this->connection->perform($sql);
        }
    }

    public function loadMappers()
    {
        $this->orm->register('images', $this->getMapperConfig('images'));
        $this->orm->register('tags', $this->getMapperConfig('tags'));
        $this->orm->register('categories', $this->getMapperConfig('categories'));
        $this->orm->register('products', $this->getMapperConfig('products'));
        $this->orm->register('content_products', $this->getMapperConfig('content_products'));

    }

    public function getMapperConfig($name)
    {
        return include(__DIR__ . '/resources/mappers/' . $name . '.php');
    }

    protected function insertRow($table, $values)
    {
        $insert = new Insert($this->connection);
        $insert->into($table)->columns($values);
        $this->connection->perform($insert->getStatement(), $insert->getBindValues());
    }

    public function assertExpectedQueries($expected)
    {
        $this->assertEquals($expected, count($this->connectionLocator->getQueries()));
    }

    public function assertRowDeleted($table, ...$conditions)
    {
        $select = new Select($this->connection);
        $row    = $select->from($table)
                         ->where(...$conditions)
                         ->fetchOne();
        $this->assertNull($row);
    }

    public function assertRowPresent($table, ...$conditions)
    {
        $select = new Select($this->connection);
        $row    = $select->from($table)
                         ->where(...$conditions)
                         ->fetchOne();
        $this->assertNotNull($row);
    }

    protected function insertRows($table, $columns, $rows)
    {
        foreach ($rows as $row) {
            $this->insertRow($table, array_combine($columns, $row));
        }
    }

    protected function assertSameStatement($expect, $actual)
    {
        $this->assertSame($this->removeWhiteSpace($expect), $this->removeWhiteSpace($actual));
    }

    protected function removeWhiteSpace($str)
    {
        $str = trim($str);
        $str = preg_replace('/^[ \t]*/m', '', $str);
        $str = preg_replace('/[ \t]*$/m', '', $str);
        $str = preg_replace('/[ ]{2,}/m', ' ', $str);
        $str = preg_replace('/[\r\n|\n|\r]+/', ' ', $str);
        $str = str_replace('( ', '(', $str);
        $str = str_replace(' )', ')', $str);

        return $str;
    }
}