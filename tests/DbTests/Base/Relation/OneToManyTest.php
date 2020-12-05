<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\DbTests\Base\Relation;

use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Entity\Tracker;
use Sirius\Orm\Mapper;
use Sirius\Orm\Query;
use Sirius\Orm\Relation\OneToMany;
use Sirius\Orm\Relation\RelationConfig;
use Sirius\Orm\Tests\BaseTestCase;
use Sirius\Orm\Tests\Generated\Mapper\CategoryMapper;
use Sirius\Orm\Tests\Generated\Mapper\ProductMapper;

class OneToManyTest extends BaseTestCase
{

    /**
     * @var CategoryMapper
     */
    protected $categoryMapper;
    /**
     * @var ProductMapper
     */
    protected $productsMapper;

    public function setUp(): void
    {
        parent::setUp();
        $this->loadMappers();

        $this->categoryMapper = $this->orm->get('categories');
        $this->productsMapper = $this->orm->get('products');
    }

    public function test_query_callback()
    {
        $relation = new OneToMany('products', $this->categoryMapper, $this->productsMapper, [
            RelationConfig::QUERY_CALLBACK => function (Query $query) {
                return $query->where('active', 1);
            }
        ]);

        $tracker = new Tracker([
            ['id' => 10],
            ['id' => 11],
        ]);
        $query   = $relation->getQuery($tracker);

        $expectedSql = <<<SQL
SELECT
    products.*
FROM
    tbl_products as products
WHERE
    (category_id IN (:__1__, :__2__) AND active = :__3__) AND deleted_on IS NULL
SQL;

        $this->assertSameStatement($expectedSql, $query->getStatement());
        $this->assertSame([
            '__1__' => [10, \PDO::PARAM_INT],
            '__2__' => [11, \PDO::PARAM_INT],
            '__3__' => [1, \PDO::PARAM_INT],
        ], $query->getBindValues());
    }

    public function test_join_with()
    {
        $query = $this->categoryMapper->newQuery()
                                      ->joinWith('products');

        $expectedStatement = <<<SQL
SELECT
    categories.*
FROM
    categories
    INNER JOIN (
    SELECT
        products.*
    FROM
        tbl_products as products
    WHERE deleted_on IS NULL    
    ) AS products ON categories.id = products.category_id 
SQL;

        $this->assertSameStatement($expectedStatement, $query->getStatement());
    }

    public function test_query_guards()
    {
        $relation = new OneToMany('products', $this->categoryMapper, $this->productsMapper, [
            RelationConfig::FOREIGN_GUARDS => ['status' => 'active']
        ]);

        $tracker = new Tracker([
            ['id' => 10],
            ['id' => 11],
        ]);
        $query   = $relation->getQuery($tracker);

        $expectedSql = <<<SQL
SELECT
    products.*
FROM
    tbl_products as products
WHERE
    (category_id IN (:__1__, :__2__)) AND deleted_on IS NULL AND status = :__3__
SQL;

        $this->assertSameStatement($expectedSql, $query->getStatement());
        $this->assertSame([
            '__1__' => [10, \PDO::PARAM_INT],
            '__2__' => [11, \PDO::PARAM_INT],
            '__3__' => ['active', \PDO::PARAM_STR],
        ], $query->getBindValues());
    }

    public function test_eager_load_executes_the_query_immediately()
    {
        $this->populateDb();

        $category = $this->categoryMapper
            ->newQuery()
            ->load('children')
            ->first();

        $this->assertExpectedQueries(2); // category + children
        $this->assertEquals(2, count($category->getChildren()));
    }

    public function test_lazy_load_executes_query_when_necessary()
    {
        $this->populateDb();

        $category = $this->categoryMapper
            ->newQuery()
            ->first();

        $this->assertExpectedQueries(1); // category only
        $this->assertEquals(2, count($category->getChildren()));
        $this->assertExpectedQueries(2); // category + products
    }

    public function test_delete_with_all_relations_when_relation_is_cascade()
    {
        $this->populateDb();

        $category = $this->categoryMapper
            ->newQuery()
            ->first();

        $this->assertTrue($this->categoryMapper->delete($category, true));
        $this->assertNull($category->getId());
        $this->assertRowDeleted('categories', 'id = 2');
        $this->assertRowDeleted('categories', 'id = 3');
        $this->assertRowDeleted('tbl_languages', 'content_id = 1');
    }

    public function test_delete_with_limited_relations_when_relation_is_cascade()
    {
        $this->populateDb();

        $category = $this->categoryMapper
            ->newQuery()
            ->first();

        $this->assertTrue($this->categoryMapper->delete($category, ['children']));
        $this->assertNull($category->getId());
        $this->assertRowDeleted('categories', 'id', 2);
        $this->assertRowDeleted('categories', 'id', 3);
        $this->assertRowPresent('tbl_languages', 'content_id = 1');
    }

    public function test_delete_without_relations()
    {
        $this->populateDb();

        $category = $this->categoryMapper
            ->newQuery()
            ->first();

        $this->assertTrue($this->categoryMapper->delete($category));
        $this->assertNull($category->getId());
        $this->assertRowPresent('categories', 'id', 2);
        $this->assertRowPresent('categories', 'id', 3);
        $this->assertRowPresent('tbl_languages', 'content_id', 1);
    }

    public function test_delete_with_cascade_false()
    {
        $this->populateDb();

        $category = $this->categoryMapper
            ->newQuery()
            ->first();

        $this->assertTrue($this->categoryMapper->delete($category, false));
        $this->assertNull($category->getId());
        $this->assertRowPresent('categories', 'id', 2);
        $this->assertRowPresent('categories', 'id', 3);
        $this->assertRowPresent('tbl_languages', 'content_id', 1);
    }

    public function test_deep_insert()
    {
        $category = $this->categoryMapper->newEntity([
            'name'     => 'New category',
            'children' => new Collection([], $this->categoryMapper->getHydrator()),
            'products' => new Collection([], $this->productsMapper->getHydrator())
        ]);

        $child = $this->categoryMapper->newEntity([
            'name' => 'New child category'
        ]);
        $category->addChild($child);

        $product = $this->productsMapper->newEntity([
            'sku' => 'New sku'
        ]);
        $products = $category->getProducts();
        $products->add($product);

        $this->categoryMapper->save($category, true);
        $this->assertEquals($category->getId(), $product->category_id);
        $this->assertEquals($category->getId(), $child->getParentId());
    }

    public function test_save_with_partial_relations()
    {
        $this->populateDb();

        $category = $this->categoryMapper
            ->newQuery()
            ->first();

        $child       = $category->getChildren()->get(0);
        $child->name = 'child updated';

        $product      = $category->getProducts()->get(0);
        $product->sku = 'sku_updated';

        $this->categoryMapper->save($category, ['products']);

        $product = $this->productsMapper->find($product->id);
        $this->assertEquals('sku_updated', $product->sku);

        $child = $this->categoryMapper->find($child->getId());
        $this->assertNotEquals('child updated', $child->getName());
    }

    public function test_save_with_relations_after_patching()
    {
        $this->populateDb();

        $category = $this->categoryMapper
            ->newQuery()
            ->first();

        $this->categoryMapper->patch($category, [
            'products' => [
                // change one
                ['id' => 1, 'sku' => 'changed_sku'],
                // add one
                ['id' => null, 'sku' => 'new_sku'],
                // omit one so it gets detached
            ]
        ]);

        $queries = $this->getQueryCount();

        $this->categoryMapper->save($category, true);

        // 2 product update queries + 1 product insert query
        $this->assertExpectedQueries($queries + 3, 1);

        $products = $this->productsMapper->where('category_id', $category->getId())->get();

        $this->assertEquals(2, $products->count());
        $this->assertEquals(['changed_sku', 'new_sku'], $products->pluck('sku'));
    }

    public function test_save_without_relations()
    {
        $this->populateDb();

        $category = $this->categoryMapper
            ->find(1);
        /** @var Collection $products */
        $products              = $category->getProducts();
        $products->get(0)->sku = 'sku_updated';

        $this->categoryMapper->save($category, false);
        $product = $this->productsMapper->find($products[0]->id);
        $this->assertEquals('sku_1', $product->sku);
    }

    public function test_aggregates()
    {
        $this->populateDb();

        $category = $this->categoryMapper
            ->newQuery()
            ->get()
            ->get(0);

        $this->assertEquals(5, $category->getLowestPrice());
        $this->assertEquals(10, $category->getHighestPrice());
        $this->assertExpectedQueries(3);
    }

    protected function populateDb(): void
    {
        $this->insertRow('categories', [
            'id'   => 1,
            'name' => 'parent'
        ]);
        $this->insertRow('categories', [
            'id'        => 2,
            'parent_id' => 1,
            'name'      => 'child 1'
        ]);
        $this->insertRow('categories', [
            'id'        => 3,
            'parent_id' => 1,
            'name'      => 'child 2'
        ]);
        $this->insertRow('tbl_languages', [
            'id'           => 3,
            'content_type' => 'categories',
            'content_id'   => 1,
            'lang'         => 'en',
            'title'        => 'parent category',
            'slug'         => 'parent-category',
        ]);
        $this->insertRow('tbl_products', [
            'id'          => 1,
            'category_id' => 1,
            'sku'         => 'sku_1',
            'price'       => 5
        ]);
        $this->insertRow('tbl_products', [
            'id'          => 2,
            'category_id' => 1,
            'sku'         => 'sku_2',
            'price'       => 10
        ]);
    }
}
