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
        $this->mapper = Mapper::make($this->orm, MapperConfig::make([
            MapperConfig::TABLE       => 'products',
            MapperConfig::TABLE_ALIAS => 'p',
            MapperConfig::COLUMNS     => ['id', 'category_id', 'featured_image_id', 'sku', 'price'],
            MapperConfig::GUARDS      => ['category_id' => 10]
        ]));

        $product = $this->mapper->newEntity(['sku' => 'abc', 'price' => 10.5, 'category_id' => 20]);
        $this->mapper->save($product);

        // reload after insert
        $product = $this->mapper->find($product->getPk());
        $this->assertEquals(10.5, $product->get('price'));
        $product->set('price', 100);
        $this->mapper->save($product);

        // reload after save
        $product = $this->mapper->find($product->getPk());
        $this->assertEquals(100, $product->get('price'));
        // verify guards
        $this->assertEquals(10, $product->get('category_id'));
        $this->assertEquals(StateEnum::SYNCHRONIZED, $product->getPersistanceState());
    }

    public function test_entity_is_reverted()
    {

        $this->mapper = Mapper::make($this->orm, MapperConfig::make([
            MapperConfig::TABLE       => 'products',
            MapperConfig::TABLE_ALIAS => 'p',
            MapperConfig::COLUMNS     => ['id', 'category_id', 'featured_image_id', 'sku', 'price'],
            MapperConfig::BEHAVIOURS  => [new \Sirius\Orm\Tests\Behaviour\FakeThrowsException()]
        ]));

        $this->insertRow('products', ['sku' => 'abc', 'price' => 10.5, 'category_id' => 20]);

        $product = $this->mapper->find(1);

        $this->expectException(\Exception::class);
        $this->mapper->save($product);
    }
}