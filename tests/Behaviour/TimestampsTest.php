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
            MapperConfig::TABLE     => 'content',
            MapperConfig::COLUMNS   => ['id', 'content_type', 'title', 'description', 'summary'],
            MapperConfig::GUARDS    => ['content_type' => 'product'],
            MapperConfig::BEHAVIOURS  => [new Timestamps()]
        ]));

        $product = $this->mapper->newEntity(['title' => 'Product 1']);

        $this->assertNull($product->get('created_at'));
        $this->assertNull($product->get('updated_at'));

        $this->assertTrue($this->mapper->save($product));

        $this->assertNotNull($product->get('created_at'));
        $this->assertNotNull($product->get('updated_at'));
    }
}