<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\Action;

use Sirius\Orm\Entity\StateEnum;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;
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

    public function test_entity_is_reverted()
    {

        $mapper = Mapper::make($this->orm, MapperConfig::fromArray([
            MapperConfig::TABLE     => 'content',
            MapperConfig::COLUMNS   => ['id', 'content_type', 'title', 'description', 'summary'],
            MapperConfig::GUARDS    => ['content_type' => 'product'],
            MapperConfig::BEHAVIOURS  => [new \Sirius\Orm\Tests\Behaviour\FakeThrowsException()]
        ]));

        $this->expectException(\Exception::class);

        $product = $mapper->newEntity(['title' => 'Product 1']);

        $this->assertNull($product->id);

        $mapper->save($product);

        $this->assertNull($product->id);
    }
}