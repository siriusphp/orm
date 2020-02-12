<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests;

use Atlas\Pdo\Connection;
use PHPUnit\Framework\TestCase;
use Sirius\Orm\ConnectionLocator;
use Sirius\Orm\Orm;
use Sirius\Sql\Insert;

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

    public function setUp(): void
    {
        parent::setUp();

        // TODO: Change connection type to MYSQL/POSTGRES on env variable
        $connection = Connection::new('sqlite::memory:');

        $this->connection = $connection;
        $connection->logQueries();
        $connectionLocator = new ConnectionLocator(function () use ($connection) {
            return $connection;
        });
        $this->orm         = new Orm($connectionLocator);
        $this->createTables();
    }

    public function createTables($fileName = 'generic')
    {
        foreach (include(__DIR__ . "/resources/tables/{$fileName}.php") as $sql) {
            $this->connection->perform($sql);
        }
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