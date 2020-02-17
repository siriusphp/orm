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
        $this->mapper = Mapper::make($this->orm, MapperConfig::fromArray([
            MapperConfig::TABLE       => 'products',
            MapperConfig::TABLE_ALIAS => 'p',
            MapperConfig::COLUMNS     => ['id', 'category_id', 'featured_image_id', 'sku', 'price']
        ]));

        $this->insertRow('products', ['sku' => 'abc', 'price' => 10.5]);

        $product = $this->mapper->find(1);
        $this->assertNotNull($product);

        $this->mapper->delete($product);
        $this->assertNull($this->mapper->find(1));
        $this->assertNull($product->getPk());
        $this->assertEquals(StateEnum::DELETED, $product->getPersistanceState());
    }

    public function test_entity_is_reverted()
    {

        $this->mapper = Mapper::make($this->orm, MapperConfig::fromArray([
            MapperConfig::TABLE       => 'products',
            MapperConfig::TABLE_ALIAS => 'p',
            MapperConfig::COLUMNS     => ['id', 'category_id', 'featured_image_id', 'sku', 'price'],
            MapperConfig::BEHAVIOURS  => [new \Sirius\Orm\Tests\Behaviour\FakeThrowsException()]
        ]));

        $this->insertRow('products', ['sku' => 'abc', 'price' => 10.5]);

        $product = $this->mapper->find(1);
        $this->assertNotNull($product);

        $this->expectException(FailedActionException::class);
        $this->mapper->delete($product);
        $this->assertEquals(1, $product->getPk());
        $this->assertEquals(StateEnum::SYNCHRONIZED, $product->getPersistanceState());
    }
}