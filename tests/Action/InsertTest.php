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
        $this->mapper = Mapper::make($this->orm, MapperConfig::make([
            MapperConfig::TABLE       => 'products',
            MapperConfig::TABLE_ALIAS => 'p',
            MapperConfig::COLUMNS     => ['id', 'category_id', 'featured_image_id', 'sku', 'price'],
            MapperConfig::GUARDS      => ['category_id' => 10]
        ]));

        $product = $this->mapper->newEntity(['sku' => 'abc', 'price' => 10.5, 'category_id' => 20]);

        $this->assertNull($product->getPk());

        $this->mapper->save($product);

        $this->assertNotNull($product->getPk());

        // verify guards
        $product = $this->mapper->find($product->getPk());
        $this->assertEquals(10, $product->get('category_id'));
    }

    public function test_entity_is_reverted()
    {

        $this->mapper = Mapper::make($this->orm, MapperConfig::make([
            MapperConfig::TABLE       => 'products',
            MapperConfig::TABLE_ALIAS => 'p',
            MapperConfig::COLUMNS     => ['id', 'category_id', 'featured_image_id', 'sku', 'price'],
            MapperConfig::BEHAVIOURS  => [new \Sirius\Orm\Tests\Behaviour\FakeThrowsException()]
        ]));

        $this->expectException(\Exception::class);

        $product = $this->mapper->newEntity(['sku' => 'abc', 'price' => 10.5, 'category_id' => 20]);

        $this->assertNull($product->getPk());

        $this->mapper->save($product);

        $this->assertNull($product->getPk());
    }
}