<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\Relation;

use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\Relation\RelationConfig;
use Sirius\Orm\Tests\BaseTestCase;

class OneToOneTest extends BaseTestCase
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
        $this->foreignMapper = $this->orm->get('content_products');
    }

    public function test_delete_with_cascade_true()
    {
        // reconfigure products-featured_image to use CASCADE
        $config             = $this->getMapperConfig('products', function ($arr) {
            $arr[MapperConfig::RELATIONS]['fields'][RelationConfig::CASCADE] = true;

            return $arr;
        });
        $this->nativeMapper = $this->orm->register('products', $config)->get('products');

        $this->insertRow('content', ['id' => 1, 'content_type' => 'product', 'title' => 'Product 1']);
        $this->insertRow('content_products', ['content_id' => 1, 'featured_image_id' => 2]);

        $product = $this->nativeMapper->find(1);
        $this->assertNotNull($product->fields);
        $this->assertTrue($this->nativeMapper->delete($product, true));
        $this->assertRowDeleted('content', 'id', 1);
        $this->assertRowDeleted('content_products', 'content_id', 1);
    }

    public function test_delete_with_cascade_false()
    {
        $this->insertRow('content', ['id' => 1, 'content_type' => 'product', 'title' => 'Product 1']);
        $this->insertRow('content_products', ['content_id' => 1, 'featured_image_id' => 2]);

        $product                            = $this->nativeMapper->find(1);
        $product->fields->featured_image_id = 3;

        $this->assertTrue($this->nativeMapper->delete($product, false));
        $this->assertRowDeleted('content', 'id', 1);
        $fields = $this->foreignMapper->find(1);
        $this->assertEquals('2', $fields->featured_image_id);
    }

    public function test_save_with_relations()
    {
        $this->insertRow('content', ['id' => 1, 'content_type' => 'product', 'title' => 'Product 1']);
        $this->insertRow('content_products', ['content_id' => 1, 'featured_image_id' => 2]);

        $product                            = $this->nativeMapper->find(1);
        $product->title                     = 'Product 2';
        $product->fields->featured_image_id = 3;

        $this->assertTrue($this->nativeMapper->save($product, true));

        $product = $this->nativeMapper->find(1);
        $this->assertEquals('Product 2', $product->title);
        $this->assertEquals(3, $product->fields->featured_image_id);
    }

    public function test_save_without_relations()
    {
        $this->insertRow('content', ['id' => 1, 'content_type' => 'product', 'title' => 'Product 1']);
        $this->insertRow('content_products', ['content_id' => 1, 'featured_image_id' => 2]);

        $product                            = $this->nativeMapper->find(1);
        $product->title                     = 'Product 2';
        $product->fields->featured_image_id = 3;

        $this->assertTrue($this->nativeMapper->save($product, false));

        $product = $this->nativeMapper->find(1);
        $this->assertEquals('Product 2', $product->title);
        $this->assertEquals(2, $product->fields->featured_image_id);
    }

    public function test_join_with()
    {
        $query = $this->nativeMapper->newQuery()
                                    ->joinWith('fields');

        // the featured_image is not a real one-to-one relation
        $expectedStatement = <<<SQL
SELECT
    content.*
FROM
    content
        INNER JOIN     (
    SELECT
        content_products.*
    FROM
        content_products
    ) AS fields ON content.id = fields.content_id
WHERE content_type = :__1__
SQL;

        $this->assertSameStatement($expectedStatement, $query->getStatement());
    }
}
