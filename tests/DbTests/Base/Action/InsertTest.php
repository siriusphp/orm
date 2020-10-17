<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\DbTests\Base\Action;

use Sirius\Orm\Tests\BaseTestCase;

class InsertTest extends BaseTestCase
{
    public function test_entity_is_inserted()
    {
        $mapper = $this->orm->get('products');

        $product = $mapper->newEntity(['title' => 'Product 1']);

        $this->assertNull($product->id);

        $mapper->save($product);

        $this->assertNotNull($product->id);

        $product = $mapper->find($product->id);
        $this->assertEquals('Product 1', $product->title);
    }

    public function test_entity_is_reverted_on_exception_thrown()
    {
        // create a clone so the ORM is not affected
        $mapper = $this->orm->get('products')->without();
        $mapper->use(new \Sirius\Orm\Tests\DbTests\Base\Behaviour\ThrowExceptionBehaviour());

        $this->expectException(\Exception::class);

        $product = $mapper->newEntity(['title' => 'Product 1']);

        $this->assertNull($product->id);

        $mapper->save($product);

        $this->assertNull($product->id);
    }
}
