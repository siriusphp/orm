<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\DbTests\Base\Behaviour;

use Sirius\Orm\Behaviour\Timestamps;
use Sirius\Orm\Mapper;
use Sirius\Orm\Tests\BaseTestCase;

class TimestampsTest extends BaseTestCase
{
    /**
     * @var Mapper
     */
    protected $mapper;

    public function test_behaviour_is_applied()
    {
        // create a clone so the ORM is not affected
        $mapper = $this->orm->get('products');

        $product = $mapper->newEntity(['sku' => 'sku_1']);

        $this->assertNull($product->created_on);
        $this->assertNull($product->updated_on);

        $this->assertTrue($mapper->save($product));

        $this->assertNotNull($product->created_on);
        $this->assertNotNull($product->updated_on);
    }



    public function test_behaviour_is_removed() {
        // create a clone so the ORM is not affected
        $mapper = $this->orm->get('products')->without('timestamps');

        $product = $mapper->newEntity(['sku' => 'sku_1']);

        $this->assertTrue($mapper->save($product));

        $updatedOn = $product->updated_on;

        sleep(2);

        $product->sku = 'sku_2';
        $mapper->save($product);

        $product = $mapper->find($product->id);

        $this->assertEquals('sku_2', $product->sku);
        $this->assertEquals($updatedOn, $product->updated_on);
    }
}
