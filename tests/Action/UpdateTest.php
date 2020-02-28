<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\Action;

use Sirius\Orm\Entity\StateEnum;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\Tests\BaseTestCase;

class UpdateTest extends BaseTestCase
{
    public function test_entity_is_updated()
    {
        $mapper = $this->orm->get('products');

        $product = $mapper->newEntity(['title' => 'Product 1']);
        $mapper->save($product);

        // reload after insert
        $product = $mapper->find($product->id);
        $product->description = 'Description product 1';
        $mapper->save($product);

        // reload after save
        $product = $mapper->find($product->id);
        $this->assertEquals('Description product 1', $product->description);
        $this->assertEquals(StateEnum::SYNCHRONIZED, $product->getPersistenceState());
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
        $product->title = 'Product 2';

        $this->expectException(\Exception::class);
        $mapper->save($product);
        $this->assertEquals(StateEnum::CHANGED, $product->getPersistenceState());
    }
}