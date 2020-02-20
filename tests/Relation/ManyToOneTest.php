<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\Relation;

use Sirius\Orm\Entity\Tracker;
use Sirius\Orm\Mapper;
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

        $this->nativeMapper  = $this->orm->get('products');
        $this->foreignMapper = $this->orm->get('categories');
    }

    public function test_query_callback()
    {
        $relation = new ManyToOne('category', $this->nativeMapper, $this->foreignMapper, [
            RelationConfig::QUERY_CALLBACK => function (Query $query) {
                return $query->where('status', 'active');
            }
        ]);

        $tracker = new Tracker($this->nativeMapper, [
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

        $tracker = new Tracker($this->nativeMapper, [
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
            ->load('category')
            ->get();

        $category1 = $products[0]->get('category');
        $category2 = $products[1]->get('category');
        $this->assertNotNull($category1);
        $this->assertEquals(10, $category1->getPk());
        $this->assertNotNull($category2);
        $this->assertSame($category1, $category2); // to ensure only one query was executed
    }

    public function test_lazy_load()
    {
        $this->populateDb();

        $products = $this->nativeMapper
            ->newQuery()
            ->get();

        $category1 = $products[0]->get('category');
        $category2 = $products[1]->get('category');
        $this->assertNotNull($category1);
        $this->assertEquals(10, $category1->getPk());
        $this->assertNotNull($category2);
        $this->assertSame($category1, $category2); // to ensure only one query was executed
    }

    public function test_delete_with_cascade_true()
    {
        $this->populateDb();

        // don't know why would anybody do this but...
        $config                                                 = $this->getMapperConfig('products');
        $config->relations['category'][RelationConfig::CASCADE] = true;
        $this->nativeMapper                                     = $this->orm->register('products', $config)->get('products');

        $product = $this->nativeMapper
            ->newQuery()
            ->first();

        $this->assertTrue($this->nativeMapper->delete($product));

        $category = $this->foreignMapper->find($product->get('category_id'));
        $this->assertNull($category);
    }

    public function test_delete_with_cascade_false()
    {
        $this->populateDb();

        $product = $this->nativeMapper
            ->newQuery()
            ->first();

        $this->assertTrue($this->nativeMapper->delete($product));

        $category = $this->foreignMapper->find($product->get('category_id'));
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

        $product->set('category', $category);

        $this->nativeMapper->save($product);
        $this->assertEquals($category->getPk(), $product->get('category_id'));
    }

    public function test_save_with_relations()
    {
        $this->populateDb();

        $product = $this->nativeMapper
            ->newQuery()
            ->first();

        $category = $product->get('category');
        $category->set('name', 'New category');

        $this->nativeMapper->save($product);
        $category = $this->foreignMapper->find($category->getPk());
        $this->assertEquals('New category', $category->get('name'));
    }

    public function test_save_without_relations()
    {
        $this->populateDb();

        $product = $this->nativeMapper
            ->newQuery()
            ->first();

        $category = $product->get('category');
        $category->set('name', 'New category');

        $this->nativeMapper->save($product, false);
        $category = $this->foreignMapper->find($category->getPk());
        $this->assertEquals('Category', $category->get('name'));
    }

    protected function populateDb(): void
    {
        $this->insertRow('categories', ['id' => 10, 'name' => 'Category']);
        $this->insertRow('products', ['category_id' => 10, 'sku' => 'abc', 'price' => 10.5]);
        $this->insertRow('products', ['category_id' => 10, 'sku' => 'xyz', 'price' => 20.5]);
    }
}