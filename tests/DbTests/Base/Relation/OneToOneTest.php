<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\DbTests\Base\Relation;

use Sirius\Orm\Mapper;
use Sirius\Orm\Tests\BaseTestCase;

class OneToOneTest extends BaseTestCase
{

    /**
     * @var Mapper
     */
    protected $productsMapper;

    /**
     * @var Mapper
     */
    protected $cascadeProductsMapper;

    /**
     * @var Mapper
     */
    protected $ebayProductsMapper;

    public function setUp(): void
    {
        parent::setUp();
        $this->loadMappers();

        $this->cascadeProductsMapper = $this->orm->get('cascade_products');
        $this->productsMapper        = $this->orm->get('products');
        $this->ebayProductsMapper    = $this->orm->get('ebay_products');
    }

    public function test_delete_with_cascade_true()
    {
        $this->insertRow('tbl_products', ['id' => 1, 'sku' => 'sku_1', 'price' => 5]);
        $this->insertRow('tbl_ebay_products', ['id' => 2, 'product_id' => 1, 'price' => 10]);

        $product = $this->productsMapper->find(1);
        $this->assertNotNull($product->ebay);
        $this->assertTrue($this->productsMapper->delete($product, true));
        $this->assertNull($this->productsMapper->find(1));
        $this->assertRowDeleted('tbl_ebay_products', 'id  = 2');
    }

    public function test_delete_with_cascade_false()
    {
        $this->insertRow('tbl_products', ['id' => 1, 'sku' => 'sku_1', 'price' => 5]);
        $this->insertRow('tbl_ebay_products', ['id' => 2, 'product_id' => 1, 'price' => 10]);

        $product = $this->cascadeProductsMapper->find(1);
        $this->assertNotNull($product->ebay);
        $this->assertTrue($this->cascadeProductsMapper->delete($product, false));
        $ebayProduct = $this->ebayProductsMapper->find(2);
        $this->assertEquals('1', $ebayProduct->getProductId());
    }

    public function test_delete_with_limited_relations_where_cascade_is_true()
    {
        $this->insertRow('tbl_products', ['id' => 1, 'sku' => 'sku_1', 'price' => 5]);
        $this->insertRow('tbl_ebay_products', ['id' => 2, 'product_id' => 1, 'price' => 10]);

        $product = $this->cascadeProductsMapper->find(1);
        $this->assertNotNull($product->ebay);
        $this->assertTrue($this->cascadeProductsMapper->delete($product, ['images']));
        $ebayProduct = $this->ebayProductsMapper->find(2);
        $this->assertEquals('1', $ebayProduct->getProductId());
    }

    public function test_save_with_all_relations()
    {
        $this->insertRow('categories', ['id' => 1, 'name' => 'category']);
        $this->insertRow('tbl_products', ['id' => 1, 'category_id' => 1, 'sku' => 'sku_1', 'price' => 5]);
        $this->insertRow('tbl_ebay_products', ['id' => 2, 'product_id' => 1, 'price' => 10]);

        $product                 = $this->productsMapper->find(1);
        $product->sku            = 'sku_updated';
        $product->category->name = 'updated_category';
        $product->ebay->setPrice(20);

        $this->assertTrue($this->productsMapper->save($product, true));

        $product = $this->productsMapper->find(1);
        $this->assertEquals('sku_updated', $product->sku);
        $this->assertEquals(20, $product->ebay->getPrice());
        $this->assertEquals('updated_category', $product->category->name);
    }


    public function test_save_with_partial_relations()
    {
        $this->insertRow('categories', ['id' => 1, 'name' => 'category']);
        $this->insertRow('tbl_products', ['id' => 1, 'category_id' => 1, 'sku' => 'sku_1', 'price' => 5]);
        $this->insertRow('tbl_ebay_products', ['id' => 2, 'product_id' => 1, 'price' => 10]);

        $product                 = $this->productsMapper->find(1);
        $product->sku            = 'sku_updated';
        $product->category->name = 'updated_category';
        $product->ebay->setPrice(20);

        $this->assertTrue($this->productsMapper->save($product, ['ebay']));

        $product = $this->productsMapper->find(1);
        $this->assertEquals('sku_updated', $product->sku);
        $this->assertEquals(20, $product->ebay->getPrice());
        $this->assertEquals('category', $product->category->name);
    }

    public function test_save_without_relations()
    {
        $this->insertRow('categories', ['id' => 1, 'name' => 'category']);
        $this->insertRow('tbl_products', ['id' => 1, 'category_id' => 1, 'sku' => 'sku_1', 'price' => 5]);
        $this->insertRow('tbl_ebay_products', ['id' => 2, 'product_id' => 1, 'price' => 10]);

        $product                 = $this->productsMapper->find(1);
        $product->sku            = 'sku_updated';
        $product->category->name = 'updated_category';
        $product->ebay->setPrice(20);

        $this->assertTrue($this->productsMapper->save($product, false));

        $product = $this->productsMapper->find(1);
        $this->assertEquals('sku_updated', $product->sku);
        $this->assertEquals(10, $product->ebay->getPrice());
        $this->assertEquals('category', $product->category->name);
    }

    public function test_join_with()
    {
        $query = $this->productsMapper->newQuery()
                                      ->joinWith('ebay');

        // the featured_image is not a real one-to-one relation
        $expectedStatement = <<<SQL
SELECT
    products.*
FROM
    tbl_products as products
        INNER JOIN     (
    SELECT
        tbl_ebay_products.*
    FROM
        tbl_ebay_products
    ) AS ebay ON products.id = ebay.product_id
WHERE deleted_on IS NULL
SQL;

        $this->assertSameStatement($expectedStatement, $query->getStatement());
    }
}
