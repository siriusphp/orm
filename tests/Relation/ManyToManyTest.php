<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\Relation;

use Sirius\Orm\Entity\Tracker;
use Sirius\Orm\Mapper;
use Sirius\Orm\Query;
use Sirius\Orm\Relation\ManyToMany;
use Sirius\Orm\Relation\RelationOption;
use Sirius\Orm\Tests\BaseTestCase;

class ManyToManyTest extends BaseTestCase
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
        $this->foreignMapper = $this->orm->get('tags');
    }

    public function test_query_callback()
    {
        $relation = new ManyToMany('tags', $this->nativeMapper, $this->foreignMapper, [
            RelationOption::QUERY_CALLBACK => function (Query $query) {
                return $query->where('status', 'active');
            }
        ]);

        $tracker = new Tracker($this->nativeMapper, [
            ['id' => 10],
            ['id' => 11],
        ]);
        $query   = $relation->getQuery($tracker);

        $expectedSql = <<<SQL
SELECT
    tags.*, products_tags.product_id
FROM
    tags
    INNER JOIN products_tags ON tags.id = products_tags.tag_id
WHERE
    product_id IN (:__1__, :__2__) AND status = :__3__
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
        $relation = new ManyToMany('category', $this->nativeMapper, $this->foreignMapper, [
            RelationOption::FOREIGN_GUARDS => ['status' => 'active', 'deleted_at IS NULL']
        ]);

        $tracker = new Tracker($this->nativeMapper, [
            ['id' => 10],
            ['id' => 11],
        ]);
        $query   = $relation->getQuery($tracker);

        $expectedSql = <<<SQL
SELECT
    tags.*, products_tags.product_id
FROM
    tags
    INNER JOIN products_tags ON tags.id = products_tags.tag_id
WHERE
    (product_id IN (:__1__, :__2__)) AND status = :__3__ AND deleted_at IS NULL
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
            ->load('tags')
            ->get();

        $tag1 = $products[0]->get('tags')[0];
        $tag2 = $products[1]->get('tags')[0];
        $this->assertNotNull($tag1);
        $this->assertNotNull($tag2);
        $this->assertEquals($tag1->getPk(), $tag2->getPk());
    }

    public function test_lazy_load()
    {
        $this->populateDb();

        $products = $this->nativeMapper
            ->newQuery()
            ->get();

        $tag1 = $products[0]->get('tags')[0];
        $tag2 = $products[1]->get('tags')[0];
        $this->assertNotNull($tag1);
        $this->assertNotNull($tag2);
        $this->assertEquals(1, $tag1->get('pivot_position'));
        $this->assertEquals(1, $tag2->get('pivot_position'));
        $this->assertEquals($tag1->getPk(), $tag2->getPk()); // the tags are not the same object (due to the pivot) but they have the same ID
    }

    public function test_delete_with_cascade_true()
    {
        $this->populateDb();

        // don't know why would anybody do this but...
        $config                                                 = $this->getMapperConfig('products');
        $config->relations['tags'][RelationOption::CASCADE] = true;
        $this->nativeMapper                                     = $this->orm->register('products', $config)->get('products');

        $product = $this->nativeMapper
            ->newQuery()
            ->first();

        $this->assertTrue($this->nativeMapper->delete($product));

        $tag = $this->foreignMapper->find(1);
        $this->assertNull($tag);
    }

    public function test_delete_with_cascade_false()
    {
        $this->populateDb();

        $product = $this->nativeMapper
            ->newQuery()
            ->first();

        $this->assertTrue($this->nativeMapper->delete($product));

        $tag = $this->foreignMapper->find(1);
        $this->assertNotNull($tag);
    }



    public function test_save_with_relations()
    {
        $this->populateDb();

        $product = $this->nativeMapper
            ->newQuery()
            ->first();

        $tag = $product->get('tags')[0];
        $tag->set('name', 'New tag');
        $tag->set('pivot_position', 3);

        $this->nativeMapper->save($product);

        $product = $this->nativeMapper->find($product->getPk());
        $tag = $product->get('tags')[0];

        $this->assertEquals('New tag', $tag->get('name'));
        $this->assertEquals(3, $tag->get('pivot_position'));
    }

    public function test_save_without_relations()
    {
        $this->populateDb();

        $product = $this->nativeMapper
            ->newQuery()
            ->first();

        $tag = $product->get('tags')[0];
        $tag->set('name', 'New tag');
        $tag->set('pivot_position', 3);

        $this->nativeMapper->save($product, false);

        $product = $this->nativeMapper->find($product->getPk());
        $tag = $product->get('tags')[0];

        $this->assertEquals('tag_1', $tag->get('name'));
        $this->assertEquals(1, $tag->get('pivot_position'));
    }

    protected function populateDb(): void
    {
        $this->insertRows('tags', ['id', 'name'], [
            [1, 'tag_1'],
            [2, 'tag_2'],
        ]);
        $this->insertRows('products', ['id', 'category_id', 'sku', 'price'], [
            [1, 10, 'abc', 10],
            [2, 10, 'xyz', 20],
        ]);
        $this->insertRows('products_tags', ['product_id', 'tag_id', 'position'], [
            [1, 1, 1],
            [1, 2, 2],
            [2, 1, 1],
        ]);
    }
}