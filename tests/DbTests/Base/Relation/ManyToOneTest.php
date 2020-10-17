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
    protected $nativeMapper;
    /**
     * @var Mapper
     */
    protected $foreignMapper;

    public function setUp(): void
    {
        parent::setUp();
        $this->loadMappers();

        $this->nativeMapper  = $this->orm->get('content_products');
        $this->foreignMapper = $this->orm->get('categories');
    }

    public function test_join_with()
    {
        $query = $this->nativeMapper->newQuery()
                                    ->joinWith('category');

        $expectedStatement = <<<SQL
SELECT
    content_products.*
FROM
    content_products
        INNER JOIN     (
    SELECT
        categories.*
    FROM
        categories
    ) AS category ON content_products.category_id = category.id
SQL;

        $this->assertSameStatement($expectedStatement, $query->getStatement());
    }

    public function test_query_callback()
    {
        $relation = new ManyToOne('category', $this->nativeMapper, $this->foreignMapper, [
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
        $relation = new ManyToOne('category', $this->nativeMapper, $this->foreignMapper, [
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

        $products = $this->nativeMapper
            ->newQuery()
            ->load('category', 'category.parent')
            ->get();

        $this->assertExpectedQueries(3); // products + category + category parent
        $category1 = $products[0]->category;
        $category2 = $products[1]->category;
        $this->assertNotNull($category1);
        $this->assertEquals(10, $category1->id);
        $this->assertNotNull($category2);
        $this->assertSame($category1, $category2); // to ensure only one query was executed
        $this->assertSame($category1->parent, $category2->parent); // to ensure only one query was executed
    }

    public function test_lazy_load()
    {
        $this->populateDb();

        $products = $this->nativeMapper
            ->newQuery()
            ->get();

        $this->assertExpectedQueries(1); // products + category + category parent
        $category1 = $products[0]->category;
        $category2 = $products[1]->category;
        $this->assertNotNull($category1);
        $this->assertEquals(10, $category1->id);
        $this->assertNotNull($category2);
        $this->assertSame($category1, $category2); // to ensure only one query was executed
        $this->assertSame($category1->parent, $category2->parent); // to ensure only one query was executed
        $this->assertExpectedQueries(3); // products + category + category parent
    }

    public function test_delete_with_cascade_true()
    {
        $this->populateDb();


        $config             = $this->getMapperConfig('content_products', function ($arr) {
            $arr[MapperConfig::RELATIONS]['category'][RelationConfig::CASCADE] = true;

            return $arr;
        });
        $this->nativeMapper = $this->orm->register('content_products', $config)->get('content_products');

        $product = $this->nativeMapper
            ->newQuery()
            ->first();

        $this->assertTrue($this->nativeMapper->delete($product, true));

        $category = $this->foreignMapper->find($product->category_id);
        $this->assertNull($category);
    }

    public function test_delete_with_cascade_false()
    {
        $this->populateDb();

        $product = $this->nativeMapper
            ->newQuery()
            ->first();

        $this->assertTrue($this->nativeMapper->delete($product));

        $category = $this->foreignMapper->find($product->category_id);
        $this->assertNotNull($category);
    }

    public function test_insert()
    {
        $this->populateDb();

        $product = $this->foreignMapper->newEntity([
            'sku' => 'New sku'
        ]);

        $category = $this->nativeMapper->newEntity([
            'name' => 'New Category'
        ]);

        $product->category = $category;

        $this->nativeMapper->save($product);
        $this->assertEquals($category->id, $product->category_id);
    }

    public function test_save_with_relations()
    {
        $this->populateDb();

        $product = $this->nativeMapper
            ->newQuery()
            ->first();

        $category       = $product->category;
        $category->name = 'New category';

        $this->nativeMapper->save($product, true);
        $category = $this->foreignMapper->find($category->id);
        $this->assertEquals('New category', $category->name);
    }

    public function test_save_without_relations()
    {
        $this->populateDb();

        $product = $this->nativeMapper
            ->newQuery()
            ->first();

        $category       = $product->category;
        $category->name = 'New category';

        $this->nativeMapper->save($product, false);
        $category = $this->foreignMapper->find($category->id);
        $this->assertEquals('Category', $category->name);
    }

    protected function populateDb(): void
    {
        $this->insertRow('categories', ['id' => 10, 'parent_id' => 20, 'name' => 'Category']);
        $this->insertRow('categories', ['id' => 20, 'name' => 'Parent']);
        $this->insertRow('content_products', ['content_id' => 10, 'category_id' => 10, 'sku' => 'abc', 'price' => 10.5]);
        $this->insertRow('content_products', ['content_id' => 20, 'category_id' => 10, 'sku' => 'xyz', 'price' => 20.5]);
    }
}
