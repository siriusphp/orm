<?php


use Atlas\Pdo\Connection;
use Sirius\Orm\ConnectionLocator;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\Orm;
use Sirius\Orm\Relation\RelationOption;
use Sirius\Sql\Insert;

require_once __DIR__ . '/AbstractTestSuite.php';

/**
 * This test suite just demonstrates the baseline performance without any kind of ORM
 * or even any other kind of slightest abstraction.
 */
class SiriusOrmTestSuite extends AbstractTestSuite
{

    private $orm;

    function initialize()
    {
        $loader = require_once "vendor/autoload.php";
        $loader->add('', __DIR__ . '/src');

        $this->con = Connection::new('sqlite::memory:');
        $this->orm = new Orm(ConnectionLocator::new($this->con));

        $this->initTables();

        $this->orm->register('authors', Mapper::make($this->orm, MapperConfig::make([
            MapperConfig::TABLE => 'author',
            MapperConfig::COLUMNS => ['id', 'first_name', 'last_name', 'email']
        ])));

        $this->orm->register('books', Mapper::make($this->orm, MapperConfig::make([
            MapperConfig::TABLE => 'book',
            MapperConfig::COLUMNS => ['id', 'title', 'isbn', 'price', 'author_id'],
            MapperConfig::RELATIONS => [
                'author' => [
                    RelationOption::FOREIGN_MAPPER => 'authors',
                    RelationOption::TYPE => RelationOption::TYPE_MANY_TO_ONE
                ]
            ]
        ])));
    }

    function clearCache()
    {
    }

    function beginTransaction()
    {
        $this->transaction = $this->con->beginTransaction();
    }

    function commit()
    {
        $this->con->commit();
    }

    function runAuthorInsertion($i)
    {
//        $insert = new Insert($this->con);
//        $insert->into('author')->columns([
//            'first_name' => 'John' . $i,
//            'last_name'  => 'Doe' . $i,
//        ]);
//        $insert->perform();
//        $this->authors[] = $this->con->lastInsertId();
//        return;

        $authorsMapper = $this->orm->get('authors');
        $author = $authorsMapper->newEntity([
            'first_name' => 'John' . $i,
            'last_name'  => 'Doe' . $i,
        ]);
        $authorsMapper->save($author);
        $this->authors[] = $this->con->lastInsertId();
    }

    function runBookInsertion($i)
    {
//        $insert = new Insert($this->con);
//        $insert->into('book')->columns([
//            'title'     => 'Hello' . $i,
//            'isbn'      => '1234' . $i,
//            'price'     => $i,
//            'author_id' => $this->authors[array_rand($this->authors)],
//        ]);
//        $insert->perform();
//        $this->books[] = $this->con->lastInsertId();
//        return;

        $booksMapper = $this->orm->get('books');
        $book = $booksMapper->newEntity([
            'title'     => 'Hello' . $i,
            'isbn'      => '1234' . $i,
            'price'     => $i,
            'author_id' => $this->authors[array_rand($this->authors)],
        ]);
        $booksMapper->save($book);
        $this->books[] = $this->con->lastInsertId();

    }

    function runPKSearch($i)
    {
        $author = $this->orm->get('authors')->find($i);
    }

    function runHydrate($i)
    {
        $stmt = $this->orm->get('books')
            ->where('price', $i, '>')
            ->limit(50)
            ->get();

    }

    function runComplexQuery($i)
    {
        $stmt = $this->orm->get('authors')
            ->newQuery()
            ->whereSprintf('id > %s OR first_name = %s ', (int)$this->authors[array_rand($this->authors)], 'John Doe')
            ->count();
    }

    function runJoinSearch($i)
    {
        $book = $this->orm->get('books')
            ->where('title', 'Hello' . $i)
            ->load('author')
            ->first();
    }

}