<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests;

use Doctrine\DBAL\Platforms\MySQL80Platform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\Schema;
use PHPUnit\Framework\TestCase;
use Sirius\Orm\Connection;
use Sirius\Orm\ConnectionLocator;
use Sirius\Orm\Helpers\Inflector;
use Sirius\Orm\Helpers\Str;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\Orm;
use Sirius\Sql\Insert;
use Sirius\Sql\Select;

class BaseTestCase extends TestCase
{

    protected $dbEngine = 'sqlite';

    protected $useGeneratedMappers = true;

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

        if ($this->dbEngine == 'mysql') {
            $connection = Connection::new(getenv('MYSQL_CONNECTION'), getenv('MYSQL_USER'), getenv('MYSQL_PASS'));
        } elseif ($this->dbEngine == 'postgres') {
            $connection = Connection::new(getenv('POSTGRES_CONNECTION'), getenv('POSTGRES_USER'), getenv('POSTGRES_PASS'));
        } else {
            $connection = Connection::new('sqlite::memory:');
        }

        $this->connection        = $connection;
        $connectionLocator       = ConnectionLocator::new($this->connection);
        $this->connectionLocator = $connectionLocator;
        $this->orm               = new Orm($connectionLocator);
        $this->createTables();
        $this->loadMappers();
        $connectionLocator->logQueries();
    }

    public function createTables()
    {
        $platform = new SqlitePlatform();
        switch ($this->dbEngine) {
            case 'mysql':
                $platform = new MySQL80Platform();
        }
        /** @var Schema $schema */
        $schema = include(__DIR__ . "/resources/schema.php");

        $schemaCreatedPath = __DIR__ . '/' . $this->dbEngine . '_schema_created';
        if (file_exists($schemaCreatedPath) && $this->dbEngine !== 'sqlite') {
            foreach ($schema->getTables() as $table) {
                $this->connection->perform('DELETE FROM ' . $table->getName());
            }
        } else {
            foreach ($schema->getTables() as $table) {
                $this->connection->perform('DROP TABLE IF EXISTS ' . $table->getName());
            }
            foreach ($schema->toSql($platform) as $table => $sql) {
                $this->connection->perform($sql);
            }
            file_put_contents($schemaCreatedPath, '1');
        }
    }

    public function loadMappers()
    {
        $mappers           = ['products', 'cascade_products', 'ebay_products', 'categories', 'languages', 'images', 'tags', 'product_languages'];
        $connectionLocator = $this->connectionLocator;

        foreach ($mappers as $name) {
            $this->orm->register($name, function ($orm) use ($name, $connectionLocator) {
                $class = 'Sirius\\Orm\\Tests\\Generated\\Mapper\\' . Str::className(Inflector::singularize($name)) . 'Mapper';
                /** @var Mapper $mapper */
                $mapper = new $class($this->orm);

                return $mapper;
            });
        }
    }

    public function getMapperConfig($name, callable $callback = null)
    {
        $mappers = include(__DIR__ . '/resources/mappers.php');
        $arr     = $mappers[$name];
        if ($callback) {
            $arr = $callback($arr);
        }

        return MapperConfig::fromArray($arr);
    }

    protected function insertRow($table, $values)
    {
        $insert = new Insert($this->connection);
        foreach ($values as $col => $value) {
            if (is_array($value)) {
                $values[$col] = json_encode($value);
            }
        }
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
        $str = preg_replace('/[\r\n|\n|\r ]+/', ' ', $str);
        $str = str_replace('( ', '(', $str);
        $str = str_replace(' )', ')', $str);

        return $str;
    }
}
