<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\DbTests\Base\Relation;

use Sirius\Orm\Entity\Tracker;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\Query;
use Sirius\Orm\Relation\ManyToMany;
use Sirius\Orm\Relation\RelationConfig;
use Sirius\Orm\Tests\BaseTestCase;

class ManyToManyTest extends BaseTestCase
{

    /**
     * @var Mapper
     */
    protected $productsMapper;
    /**
     * @var Mapper
     */
    protected $tagsMapper;

    public function setUp(): void
    {
        parent::setUp();
        $this->loadMappers();

        $this->productsMapper = $this->orm->get('products');
        $this->tagsMapper     = $this->orm->get('tags');
    }

    public function test_join_with()
    {
        $query = $this->productsMapper->newQuery()
                                      ->joinWith('tags');

        $expectedStatement = <<<SQL
SELECT
    products.*
FROM
    tbl_products as products
    INNER JOIN (
    SELECT
        tags.*,
        products_to_tags.position AS position_in_product,
        products_to_tags.tagable_id
    FROM
        tags
            INNER JOIN tbl_links_to_tags as products_to_tags ON tags.id = products_to_tags.tag_id
    ORDER BY
        position ASC
    ) AS tags ON products.id = tags.tagable_id
    WHERE deleted_on IS NULL
SQL;

        $this->assertSameStatement($expectedStatement, $query->getStatement());
    }

    public function test_query_callback()
    {
        $relation = new ManyToMany('tags', $this->productsMapper, $this->tagsMapper, [
            RelationConfig::THROUGH_TABLE         => 'products_tags',
            RelationConfig::THROUGH_NATIVE_COLUMN => 'product_id',
            RelationConfig::THROUGH_COLUMNS       => ['position' => 'pivot_position'],
            RelationConfig::QUERY_CALLBACK        => function (Query $query) {
                return $query->where('status', 'active');
            }
        ]);

        $tracker = new Tracker([
            ['id' => 10],
            ['id' => 11],
        ]);
        $query   = $relation->getQuery($tracker);

        $expectedSql = <<<SQL
SELECT
    tags.*, products_tags.position AS pivot_position, products_tags.product_id
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
        $relation = new ManyToMany('category', $this->productsMapper, $this->tagsMapper, [
            RelationConfig::THROUGH_TABLE         => 'products_tags',
            RelationConfig::THROUGH_NATIVE_COLUMN => 'product_id',
            RelationConfig::FOREIGN_GUARDS        => ['status' => 'active', 'deleted_at IS NULL']
        ]);

        $tracker = new Tracker([
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

        $products = $this->productsMapper
            ->newQuery()
            ->load('tags')
            ->get();

        $this->assertExpectedQueries(2); // products + tags
        $tag1 = $products[0]->tags[0];
        $tag2 = $products[1]->tags[0];
        $this->assertNotNull($tag1);
        $this->assertNotNull($tag2);
        $this->assertEquals($tag1->id, $tag2->id);
    }

    public function test_lazy_load()
    {
        $this->populateDb();

        $products = $this->productsMapper
            ->newQuery()
            ->get();

        $this->assertExpectedQueries(1); // products
        $tag1 = $products[0]->tags[0];
        $tag2 = $products[1]->tags[0];
        $this->assertNotNull($tag1);
        $this->assertNotNull($tag2);
        $this->assertEquals(1, $tag1->position_in_product);
        $this->assertEquals(1, $tag2->position_in_product);
        $this->assertEquals($tag1->id, $tag2->id); // the tags are not the same object (due to the pivot) but they have the same ID
        $this->assertExpectedQueries(2); // products + tags
    }

    public function test_aggregates()
    {
        $this->populateDb();

        $product  = $this->productsMapper->find(1, ['tags_count']);
        $product2 = $this->productsMapper->find(2, ['tags_count']);

        $this->assertExpectedQueries(4);
        $this->assertEquals(2, $product->tags_count);
        $this->assertEquals(1, $product2->tags_count);
    }

    public function test_save_with_relations()
    {
        $this->populateDb();

        $product = $this->productsMapper
            ->newQuery()
            ->first();

        $tag                      = $product->tags[0];
        $tag->name                = 'New tag';
        $tag->position_in_product = 3;

        $this->productsMapper->save($product, true);

        $product    = $this->productsMapper->find($product->id);
        $updatedTag = null;
        foreach ($product->tags as $tag) {
            if ( ! $updatedTag && $tag->name == 'New tag') {
                $updatedTag = $tag;
            }
        }

        $this->assertNotNull($updatedTag);
        $this->assertEquals('New tag', $updatedTag->name);
        $this->assertEquals(3, $updatedTag->position_in_product);
    }

    public function test_save_without_relations()
    {
        $this->populateDb();

        $product = $this->productsMapper
            ->newQuery()
            ->first();

        $tag                      = $product->tags[0];
        $tag->name                = 'New tag';
        $tag->position_in_product = 3;

        $this->productsMapper->save($product, false);

        $product = $this->productsMapper->find($product->id);
        $tag     = $product->tags[0];

        $this->assertEquals('tag_1', $tag->name);
        $this->assertEquals(1, $tag->position_in_product);
    }

    protected function populateDb(): void
    {
        $this->insertRows('tags', ['id', 'name'], [
            [10, 'tag_1'],
            [20, 'tag_2'],
        ]);
        $this->insertRows('tbl_products', ['id', 'sku', 'price'], [
            [1, 'sku_1', 3],
            [2, 'sku_2', 4],
        ]);
        $this->insertRows('tbl_links_to_tags', ['tagable_id', 'tagable_type', 'tag_id', 'position'], [
            [1, 'products', 10, 1],
            [1, 'products', 20, 2],
            [2, 'products', 10, 1],
        ]);
    }
}
