<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\Behaviour;

use Sirius\Orm\Behaviour\SoftDelete;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\Tests\BaseTestCase;

class SoftDeleteTest extends BaseTestCase
{
    /**
     * @var Mapper
     */
    protected $mapper;

    public function test_behaviour_is_applied()
    {
        $mockAction = \Mockery::mock(\Sirius\Orm\Action\SoftDelete::class);
        $mockAction->shouldReceive('run')->andReturn(true);

        $mockBehaviour = \Mockery::mock(SoftDelete::class);
        $mockBehaviour->shouldReceive('getName')->andReturn('soft_delete');
        $mockBehaviour->shouldNotReceive('onDelete')->andReturn($mockAction);

        $this->mapper = Mapper::make($this->orm, MapperConfig::make([
            MapperConfig::TABLE       => 'products',
            MapperConfig::TABLE_ALIAS => 'p',
            MapperConfig::COLUMNS     => ['id', 'category_id', 'featured_image_id', 'sku', 'price'],
            MapperConfig::BEHAVIOURS  => [$mockBehaviour]
        ]));

        $this->insertRow('products', ['sku' => 'abc', 'price' => 10.5]);

        $this->assertTrue($this->mapper->delete($this->mapper->find(1)));
    }
}