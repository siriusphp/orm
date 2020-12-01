<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\DbTests\Base\Action;

use Sirius\Orm\Entity\StateEnum;
use Sirius\Orm\Tests\BaseTestCase;
use Sirius\Orm\Tests\DbTests\Base\Behaviour\ThrowExceptionBehaviour;

class UpdateTest extends BaseTestCase
{
    public function test_entity_is_updated()
    {
        $mapper = $this->orm->get('products');

        $product = $mapper->newEntity(['sku' => 'sku_1']);
        $mapper->save($product);

        // reload after insert
        $product      = $mapper->find($product->id);
        $product->sku = 'sku_2';
        $mapper->save($product);

        // reload after save
        $product = $mapper->find($product->id);
        $this->assertEquals('sku_2', $product->sku);
        $this->assertEquals(StateEnum::SYNCHRONIZED, $product->getState());
    }

    public function test_column_is_mapped_to_attribute()
    {
        $mapper = $this->orm->get('products');

        $this->insertRow('tbl_products', ['id' => 1, 'sku' => 'product_1', 'price' => 10]);

        $product = $mapper->find(1);
        $this->assertEquals(10, $product->value);

        $product->value = 20;

        $mapper->save($product);
        $product = $mapper->find(1);
        $this->assertEquals(20, $product->value);
    }
}
