<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\DbTests\BaseGenerated\Mapper;

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

        // test restroe
        $this->mapper->restore(1);
        $product = $this->mapper->find(1);
        $this->assertNotNull($product);

        // test force delete
        $this->mapper->forceDelete($product);
        $this->assertRowDeleted('tbl_products', 'id = 1');
    }
}
