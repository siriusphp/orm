<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\DbTests\Base\Relation;

use Sirius\Orm\Entity\Tracker;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\Query;
use Sirius\Orm\Relation\ManyToOne;
use Sirius\Orm\Relation\RelationConfig;
use Sirius\Orm\Tests\BaseTestCase;

class ManyToOneTest extends BaseTestCase
{

    /**
     * @var Mapper
     */
    protected $productsMapper;
    /**
     * @var Mapper
     */
    protected $categoriesMapper;

    public function setUp(): void
    {
        parent::setUp();
        $this->loadMappers();

        $this->productsMapper   = $this->orm->get('products');
        $this->categoriesMapper = $this->orm->get('categories');
    }

    public function test_join_with()
    {
        $query = $this->productsMapper->newQuery()
                                      ->joinWith('category');

        $expectedStatement = <<<SQL
SELECT
    products.*
FROM
    tbl_products as products
        INNER JOIN     (
    SELECT
        categories.*
    FROM
        categories
    ) AS category ON products.category_id = category.id
    WHERE deleted_on IS NULL
SQL;

        $this->assertSameStatement($expectedStatement, $query->getStatement());
    }

    public function test_query_callback()
    {
        $relation = new ManyToOne('category', $this->productsMapper, $this->categoriesMapper, [
            RelationConfig::QUERY_CALLBACK => function (Query $query) {
                return $query->where('status', 'active');
            }
        ]);

        $tracker = new Tracker([
            ['category_id' => 10],
            ['category_id' => 11],
        ]);
        $query   = $relation->getQuery($tracker);

        $expectedSql = <<<SQL
SELECT
    categories.*
FROM
    categories
WHERE
    id IN (:__1__, :__2__) AND status = :__3__
SQL;

        $this->assertSameStatement($expectedSql, $query->getStatement());
        $this->assertSame([
            '__1__' => [10, \PDO::PARAM_INT],
            '__2__' => [11, \PDO::PARAM_INT],
            '__3__' => ['active', \PDO::PARAM_STR],
        ], $query->getBindValues());
    }

    public function test_query_guards()
    {
        $relation = new ManyToOne('category', $this->productsMapper, $this->categoriesMapper, [
            RelationConfig::FOREIGN_GUARDS => ['status' => 'active', 'deleted_at IS NULL']
        ]);

        $tracker = new Tracker([
            ['category_id' => 10],
            ['category_id' => 11],
        ]);
        $query   = $relation->getQuery($tracker);

        $expectedSql = <<<SQL
SELECT
    categories.*
FROM
    categories
WHERE
    (id IN (:__1__, :__2__)) AND status = :__3__ AND deleted_at IS NULL
SQL;

        $this->assertSameStatement($expectedSql, $query->getStatement());
        $this->assertSame([
            '__1__' => [10, \PDO::PARAM_INT],
            '__2__' => [11, \PDO::PARAM_INT],
            '__3__' => ['active', \PDO::PARAM_STR],
        ], $query->getBindValues());
    }

    public function test_eager_load()
    {
        $this->populateDb();

        $products = $this->productsMapper
            ->newQuery()
            ->load('category', 'category.parent')
            ->get();

        $this->assertExpectedQueries(3); // products + category + category parent
        $category1 = $products[0]->category;
        $category2 = $products[1]->category;
        $this->assertNotNull($category1);
        $this->assertEquals(20, $category1->id);
        $this->assertNotNull($category2);
        $this->assertSame($category1, $category2); // to ensure only one query was executed
        $this->assertSame($category1->parent, $category2->parent); // to ensure only one query was executed
        $this->assertExpectedQueries(3); // products + category + category parent
    }

    public function test_lazy_load()
    {
        $this->populateDb();

        $products = $this->productsMapper
            ->newQuery()
            ->get();

        $this->assertExpectedQueries(1); // products + category + category parent
        $category1 = $products[0]->category;
        $category2 = $products[1]->category;
        $this->assertNotNull($category1);
        $this->assertEquals(20, $category1->id);
        $this->assertNotNull($category2);
        $this->assertSame($category1, $category2); // to ensure only one query was executed
        $this->assertSame($category1->parent, $category2->parent); // to ensure only one query was executed
        $this->assertExpectedQueries(3); // products + category + category parent
    }

    public function test_delete_with_cascade_true()
    {
        $this->populateDb();

        $product = $this->productsMapper->find(1);

        $this->assertTrue($this->productsMapper->delete($product, 'cascade_category'));

        $this->assertRowPresent('categories', 'id = 10');
        $this->assertRowDeleted('categories', 'id = 20');
    }

    public function test_delete_with_cascade_false()
    {
        $this->populateDb();

        $product = $this->productsMapper
            ->newQuery()
            ->first();

        $this->assertTrue($this->productsMapper->delete($product));

        $category = $this->categoriesMapper->find($product->category_id);
        $this->assertNotNull($category);
    }

    public function test_insert_with_relations()
    {
        $this->populateDb();

        $product = $this->productsMapper->newEntity([
            'sku'   => 'New sku',
            'price' => 5,
        ]);

        $category = $this->categoriesMapper->newEntity([
            'name' => 'New Category'
        ]);

        $product->category = $category;

        $this->productsMapper->save($product, true);
        $this->assertEquals($category->id, $product->category_id);
    }

    public function test_save_with_relations()
    {
        $this->populateDb();

        $product = $this->productsMapper
            ->newQuery()
            ->first();

        $category       = $product->category;
        $category->name = 'New category';

        $this->productsMapper->save($product, true);
        $category = $this->categoriesMapper->find($category->id);
        $this->assertEquals('New category', $category->name);
    }

    public function test_save_without_relations()
    {
        $this->populateDb();

        $product = $this->productsMapper
            ->newQuery()
            ->first();

        $category       = $product->category;
        $category->name = 'New category';

        $this->productsMapper->save($product, false);
        $category = $this->categoriesMapper->find($category->id);
        $this->assertEquals('Category', $category->name);
    }

    protected function populateDb(): void
    {
        $this->insertRow('categories', ['id' => 10, 'name' => 'Parent']);
        $this->insertRow('categories', ['id' => 20, 'parent_id' => 10, 'name' => 'Category']);

        $this->insertRow('tbl_products', ['id' => 1, 'category_id' => 20, 'sku' => 'abc', 'price' => 10.5]);
        $this->insertRow('tbl_products', ['id' => 2, 'category_id' => 20, 'sku' => 'xyz', 'price' => 20.5]);
    }
}
