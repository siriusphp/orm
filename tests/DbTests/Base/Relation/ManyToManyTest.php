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

    public function test_join_with()
    {
        $query = $this->nativeMapper->newQuery()
                                    ->joinWith('tags');

        $expectedStatement = <<<SQL
SELECT
    content.*
FROM
    content
    INNER JOIN (
    SELECT
        tags.*,
        products_tags.position AS pivot_position,
        products_tags.product_id
    FROM
        tags
            INNER JOIN products_tags ON tags.id = products_tags.tag_id
    ORDER BY
        position ASC
    ) AS tags ON content.id = tags.id
    WHERE content_type = :__1__
SQL;

        $this->assertSameStatement($expectedStatement, $query->getStatement());
    }

    public function test_query_callback()
    {
        $relation = new ManyToMany('tags', $this->nativeMapper, $this->foreignMapper, [
            RelationConfig::THROUGH_TABLE         => 'products_tags',
            RelationConfig::THROUGH_NATIVE_COLUMN => 'product_id',
            RelationConfig::THROUGH_COLUMNS       => ['position'],
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
        $relation = new ManyToMany('category', $this->nativeMapper, $this->foreignMapper, [
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

        $products = $this->nativeMapper
            ->newQuery()
            ->load('tags')
            ->get();

        $this->assertExpectedQueries(3); // products + fields + tags
        $tag1 = $products[0]->tags[0];
        $tag2 = $products[1]->tags[0];
        $this->assertNotNull($tag1);
        $this->assertNotNull($tag2);
        $this->assertEquals($tag1->id, $tag2->id);
    }

    public function test_lazy_load()
    {
        $this->populateDb();

        $products = $this->nativeMapper
            ->newQuery()
            ->get();

        $this->assertExpectedQueries(2); // products + fields
        $tag1 = $products[0]->tags[0];
        $tag2 = $products[1]->tags[0];
        $this->assertNotNull($tag1);
        $this->assertNotNull($tag2);
        $this->assertEquals(1, $tag1->pivot_position);
        $this->assertEquals(1, $tag2->pivot_position);
        $this->assertEquals($tag1->id, $tag2->id); // the tags are not the same object (due to the pivot) but they have the same ID
        $this->assertExpectedQueries(3); // products + fields + tags
    }

    public function test_delete_with_cascade_true()
    {
        $this->populateDb();

        // reconfigure products-featured_image to use CASCADE
        $config             = $this->getMapperConfig('products', function ($arr) {
            $arr[MapperConfig::RELATIONS]['tags'][RelationConfig::CASCADE] = true;

            return $arr;
        });
        $this->nativeMapper = $this->orm->register('products', $config)->get('products');

        $product = $this->nativeMapper
            ->newQuery()
            ->first();

        $this->assertTrue($this->nativeMapper->delete($product, true));

        $tag = $this->foreignMapper->find(1);
        $this->assertNull($tag);
        $this->assertRowDeleted('products_tags', 'product_id = 1 AND tag_id = 1');
        $this->assertRowDeleted('products_tags', 'product_id = 1 AND tag_id = 2');
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

    public function test_aggregates()
    {
        $this->populateDb();

        $product = $this->nativeMapper->find(1, ['tags_count']);

        $this->assertExpectedQueries(3);
        $this->assertEquals(2, $product->tags_count);
        $this->assertEquals(2, $product->tags_count);
    }

    public function test_save_with_relations()
    {
        $this->populateDb();

        $product = $this->nativeMapper
            ->newQuery()
            ->first();

        $tag                 = $product->tags[0];
        $tag->name           = 'New tag';
        $tag->pivot_position = 3;

        $this->nativeMapper->save($product, true);

        $product    = $this->nativeMapper->find($product->id);
        $updatedTag = null;
        foreach ($product->tags as $tag) {
            if ( ! $updatedTag && $tag->name == 'New tag') {
                $updatedTag = $tag;
            }
        }

        $this->assertNotNull($updatedTag);
        $this->assertEquals('New tag', $updatedTag->name);
        $this->assertEquals(3, $updatedTag->pivot_position);
    }

    public function test_save_without_relations()
    {
        $this->populateDb();

        $product = $this->nativeMapper
            ->newQuery()
            ->first();

        $tag                 = $product->tags[0];
        $tag->name           = 'New tag';
        $tag->pivot_position = 3;

        $this->nativeMapper->save($product, false);

        $product = $this->nativeMapper->find($product->id);
        $tag     = $product->tags[0];

        $this->assertEquals('tag_1', $tag->name);
        $this->assertEquals(1, $tag->pivot_position);
    }

    protected function populateDb(): void
    {
        $this->insertRows('tags', ['id', 'name'], [
            [1, 'tag_1'],
            [2, 'tag_2'],
        ]);
        $this->insertRows('content', ['id', 'content_type', 'title', 'description'], [
            [1, 'product', 'Product 1', 'Product description 1'],
            [2, 'product', 'Product 2', 'Product description 2'],
        ]);
        $this->insertRows('content_products', ['content_id', 'category_id', 'sku', 'price'], [
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
