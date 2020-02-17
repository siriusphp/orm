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
        $this->mapper = Mapper::make($this->orm, MapperConfig::fromArray([
            MapperConfig::TABLE       => 'products',
            MapperConfig::TABLE_ALIAS => 'p',
            MapperConfig::COLUMNS     => ['id', 'category_id', 'featured_image_id', 'sku', 'price'],
            MapperConfig::BEHAVIOURS  => [new SoftDelete()]
        ]));

        $this->insertRow('products', ['sku' => 'abc', 'price' => 10.5]);

        $this->assertTrue($this->mapper->delete($this->mapper->find(1)));
        $this->assertRowPresent('products', 'id = 1');

        // check the mapper doesn't find the row
        $this->assertNull($this->mapper->find(1));

        // mapper without the behaviour will find the row
        $this->assertNotNull($this->mapper->without('soft_delete')->find(1));
    }
}