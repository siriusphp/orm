<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\DbTests\BaseGenerated\Mapper;

use Sirius\Orm\Entity\StateEnum;
use Sirius\Orm\Tests\BaseTestCase;
use Sirius\Orm\Tests\Generated\Mapper\ProductMapper;

class ProductMapperTest extends BaseTestCase
{
    protected $useGeneratedMappers = true;
    /**
     * @var ProductMapper
     */
    protected $mapper;

    public function setUp(): void
    {
        parent::setUp();
        $this->mapper = $this->orm->get('products');
    }

    public function test_soft_delete()
    {
        $this->insertRow('tbl_products', ['id' => 1, 'sku' => 'sku_1']);

        $product = $this->mapper->find(1);
        $this->assertNotNull($product);

        $this->mapper->delete($product);
        $this->assertRowPresent('tbl_products', 'id = 1');

        $this->assertNull($this->mapper->find(1));

        // test query with thrashed
        $this->assertNotNull($this->mapper->newQuery()->withTrashed()->find(1));

        // test restore
        $this->mapper->restore(1);
        $product = $this->mapper->find(1);
        $this->assertNotNull($product);

        // test force delete
        $this->mapper->forceDelete($product);
        $this->assertRowDeleted('tbl_products', 'id = 1');
    }

    public function test_timestamps()
    {
        $product = $this->mapper->newEntity(['sku' => 'sku_1']);
        $product->setState(StateEnum::NEW);
        $this->mapper->save($product);
        $this->assertNotNull($product->created_on);
    }

    public function test_deep_save()
    {
        $product = $this->mapper->newEntity([
            'sku'    => 'sku_1',
            'images' => [
                [
                    'name' => 'a.jpg'
                ],
                [
                    'name' => 'b.jpg'
                ],
            ]
        ]);
        print_r($product->images);
        $this->mapper->save($product, true);

        $product = $this->mapper->find($product->id);
        print_r($product->images->toArray());
    }

    public function test_aggregates_for_tags()
    {
        $this->insertRow('tbl_products', ['id' => 1, 'sku' => 'sku_1']);
        $this->insertRow('tags', ['id' => 1, 'name' => 'a']);
        $this->insertRow('tags', ['id' => 2, 'name' => 'b']);
        $this->insertRow('tbl_links_to_tags', ['tagable_id' => 1, 'tagable_type' => 'product', 'tag_id' => 1]);
        $this->insertRow('tbl_links_to_tags', ['tagable_id' => 1, 'tagable_type' => 'product', 'tag_id' => 2]);
        $this->insertRow('tbl_links_to_tags', ['tagable_id' => 1, 'tagable_type' => 'product', 'tag_id' => 3]);
        $this->insertRow('tbl_links_to_tags', ['tagable_id' => 1, 'tagable_type' => 'category', 'tag_id' => 3]);

        $product = $this->mapper->find(1);
        $this->assertEquals(2, $product->tags_count);
    }

    public function test_foreign_guards_for_images()
    {
        $this->insertRow('tbl_products', ['id' => 1, 'sku' => 'sku_1']);
        $this->insertRow('images', ['content_id' => 1, 'content_type' => 'products', 'name' => 'a.jpg']);
        $this->insertRow('images', ['content_id' => 1, 'content_type' => 'categories', 'name' => 'b.jpg']);

        $product = $this->mapper->find(1);
        $this->assertEquals(1, count($product->images));
    }

    public function test_json_attribute()
    {
        /**
         * @todo write tests
         */
    }


}
