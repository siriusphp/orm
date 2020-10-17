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

        $this->insertRow('content', ['content_type' => 'product', 'title' => 'Product 1']);

        $product = $mapper->find(1);
        $this->assertNotNull($product);

        $mapper->delete($product);
        $this->assertNull($mapper->find(1));
        $this->assertNull($product->id);
        $this->assertEquals(StateEnum::DELETED, $product->getState());
    }

    public function test_entity_is_reverted_on_exception()
    {
        // create a clone so the ORM is not affected
        $mapper = $this->orm->get('products')->without();
        $mapper->use(new ThrowExceptionBehaviour());

        $this->insertRow('content', ['content_type' => 'product', 'title' => 'Product 1']);

        $product = $mapper->find(1);
        $this->assertNotNull($product);

        $this->expectException(FailedActionException::class);
        $mapper->delete($product);
        $this->assertEquals(1, $product->id);
        $this->assertEquals(StateEnum::SYNCHRONIZED, $product->getState());
    }
}
