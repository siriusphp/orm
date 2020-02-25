<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\Action;

use Sirius\Orm\Exception\FailedActionException;
use Sirius\Orm\Entity\StateEnum;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\Tests\BaseTestCase;

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
        $this->assertNull($product->getPk());
        $this->assertEquals(StateEnum::DELETED, $product->getPersistenceState());
    }

    public function test_entity_is_reverted()
    {

        $mapper = Mapper::make($this->orm, MapperConfig::fromArray([
            MapperConfig::TABLE     => 'content',
            MapperConfig::COLUMNS   => ['id', 'content_type', 'title', 'description', 'summary'],
            MapperConfig::GUARDS    => ['content_type' => 'product'],
            MapperConfig::BEHAVIOURS  => [new \Sirius\Orm\Tests\Behaviour\FakeThrowsException()]
        ]));


        $this->insertRow('content', ['content_type' => 'product', 'title' => 'Product 1']);

        $product = $mapper->find(1);
        $this->assertNotNull($product);

        $this->expectException(FailedActionException::class);
        $mapper->delete($product);
        $this->assertEquals(1, $product->getPk());
        $this->assertEquals(StateEnum::SYNCHRONIZED, $product->getPersistenceState());
    }
}