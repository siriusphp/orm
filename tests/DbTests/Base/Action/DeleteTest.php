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
        $this->assertNull($product->id);
        $this->assertEquals(StateEnum::DELETED, $product->getState());
    }

    public function test_entity_is_reverted_on_exception_thrown_during_delete()
    {
        // create a clone so the ORM is not affected
        $mapper = $this->orm->get('products')->without();
        $mapper->use(new ThrowExceptionBehaviour());

        $attrs = ['hide_in_store' => true];
        $this->insertRow('tbl_products', [
            'id'         => 1,
            'sku'        => 'sku_1',
            'price'      => 13.5,
            'attributes' => $attrs
        ]);

        $product = $mapper->find(1);
        $this->assertNotNull($product);

        $this->expectException(FailedActionException::class);
        $mapper->delete($product);
        $this->assertEquals(1, $product->id);
        $this->assertEquals(StateEnum::SYNCHRONIZED, $product->getState());
    }

}
