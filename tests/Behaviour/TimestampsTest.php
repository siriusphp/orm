<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\Behaviour;

use Sirius\Orm\Behaviour\SoftDelete;
use Sirius\Orm\Behaviour\Timestamps;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\Tests\BaseTestCase;

class TimestampsTest extends BaseTestCase
{
    /**
     * @var Mapper
     */
    protected $mapper;

    public function test_behaviour_is_applied()
    {
        $this->mapper = Mapper::make($this->orm, MapperConfig::fromArray([
            MapperConfig::TABLE       => 'products',
            MapperConfig::TABLE_ALIAS => 'p',
            MapperConfig::COLUMNS     => ['id', 'category_id', 'featured_image_id', 'sku', 'price'],
            MapperConfig::BEHAVIOURS  => [new Timestamps()]
        ]));

        $product = $this->mapper->newEntity([
            'sku' => 'sku_1',
            'price' => 10,
        ]);

        $this->assertNull($product->get('created_at'));
        $this->assertNull($product->get('updated_at'));

        $this->assertTrue($this->mapper->save($product));

        $this->assertNotNull($product->get('created_at'));
        $this->assertNotNull($product->get('updated_at'));
    }
}