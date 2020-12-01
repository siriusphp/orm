<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\DbTests\Base\Action;

use Sirius\Orm\Entity\StateEnum;
use Sirius\Orm\Exception\FailedActionException;
use Sirius\Orm\Tests\BaseTestCase;
use Sirius\Orm\Tests\DbTests\Base\Behaviour\ThrowExceptionBehaviour;

class DeleteTest extends BaseTestCase
{
    public function test_entity_is_deleted()
    {
        $mapper = $this->orm->get('products');

        $attrs = ['hide_in_store' => true];
        $this->insertRow('tbl_products', [
            'id'         => 1,
            'sku'        => 'sku_1',
            'price'      => 13.5,
            'attributes' => $attrs
        ]);

        $product = $mapper->find(1);
        $this->assertNotNull($product);
        $this->assertTrue($product->attributes['hide_in_store']);

        $mapper->delete($product);
        $this->assertNull($mapper->find(1));
        $this->assertEquals(StateEnum::DELETED, $product->getState());
    }
}
