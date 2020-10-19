<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\DbTests\Base\Relation;

use Sirius\Orm\DynamicMapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\Relation\RelationConfig;
use Sirius\Orm\Tests\BaseTestCase;

class OneToOneTest extends BaseTestCase
{

    /**
     * @var DynamicMapper
     */
    protected $productsMapper;
    /**
     * @var DynamicMapper
     */
    protected $ebayProductsMapper;

    public function setUp(): void
    {
        parent::setUp();
        $this->loadMappers();

        $this->productsMapper     = $this->orm->get('products');
        $this->ebayProductsMapper = $this->orm->get('ebay_products');
    }

    public function test_delete_with_cascade_true()
    {
        // reconfigure products-featured_image to use CASCADE
        $config               = $this->getMapperConfig('products', function ($arr) {
            $arr[MapperConfig::RELATIONS]['ebay'][RelationConfig::CASCADE] = true;

            return $arr;
        });
        $this->productsMapper = $this->orm->register('products', $config)->get('products');

        $this->insertRow('tbl_products', ['id' => 1, 'sku' => 'sku_1', 'price' => 5]);
        $this->insertRow('tbl_ebay_products', ['id' => 2, 'product_id' => 1, 'price' => 10]);

        $product = $this->productsMapper->find(1);
        $this->assertNotNull($product->ebay);
        $this->assertTrue($this->productsMapper->delete($product, true));
        $this->assertRowDeleted('tbl_products', 'id', 1);
        $this->assertRowDeleted('tbl_ebay_products', 'id', 2);
    }

    public function test_delete_with_cascade_false()
    {
        $this->insertRow('tbl_products', ['id' => 1, 'sku' => 'sku_1', 'price' => 5]);
        $this->insertRow('tbl_ebay_products', ['id' => 2, 'product_id' => 1, 'price' => 10]);

        $product = $this->productsMapper->find(1);
        $this->assertNotNull($product->ebay);
        $this->assertTrue($this->productsMapper->delete($product, true));
        $ebayProduct = $this->ebayProductsMapper->find(2);
        $this->assertEquals('1', $ebayProduct->product_id);
    }

    public function test_save_with_all_relations()
    {
        $this->insertRow('categories', ['id' => 1, 'name' => 'category']);
        $this->insertRow('tbl_products', ['id' => 1, 'category_id' => 1, 'sku' => 'sku_1', 'price' => 5]);
        $this->insertRow('tbl_ebay_products', ['id' => 2, 'product_id' => 1, 'price' => 10]);

        $product                 = $this->productsMapper->find(1);
        $product->sku            = 'sku_updated';
        $product->category->name = 'updated_category';
        $product->ebay->price    = 20;

        $this->assertTrue($this->productsMapper->save($product, true));

        $product = $this->productsMapper->find(1);
        $this->assertEquals('sku_updated', $product->sku);
        $this->assertEquals(20, $product->ebay->price);
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
        $product->ebay->price    = 20;

        $this->assertTrue($this->productsMapper->save($product, ['ebay']));

        $product = $this->productsMapper->find(1);
        $this->assertEquals('sku_updated', $product->sku);
        $this->assertEquals(20, $product->ebay->price);
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
        $product->ebay->price    = 20;

        $this->assertTrue($this->productsMapper->save($product, false));

        $product = $this->productsMapper->find(1);
        $this->assertEquals('sku_updated', $product->sku);
        $this->assertEquals(10, $product->ebay->price);
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
SQL;

        $this->assertSameStatement($expectedStatement, $query->getStatement());
    }
}
