<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\DbTests\Base\Action;

use Sirius\Orm\Tests\BaseTestCase;

class InsertTest extends BaseTestCase
{
    public function test_entity_is_inserted()
    {
        $mapper = $this->orm->get('products');

        $product = $mapper->newEntity(['sku' => 'sku_1']);

        $this->assertNull($product->id);

        $mapper->save($product);

        $this->assertNotNull($product->id);

        $product = $mapper->find($product->id);
        $this->assertEquals('sku_1', $product->sku);
    }
}
