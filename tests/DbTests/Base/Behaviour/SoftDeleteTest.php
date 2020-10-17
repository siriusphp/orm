<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\DbTests\Base\Behaviour;

use Sirius\Orm\Behaviour\SoftDelete;
use Sirius\Orm\Tests\BaseTestCase;

class SoftDeleteTest extends BaseTestCase
{
    public function test_behaviour_is_applied()
    {
        // create a clone so the ORM is not affected
        $mapper = $this->orm->get('products')->without();
        $mapper->use(new SoftDelete());

        $this->insertRow('content', ['content_type' => 'product', 'title' => 'Product 1']);

        $this->assertTrue($mapper->delete($mapper->find(1)));
        $this->assertRowPresent('content', 'id = 1');

        // check the mapper doesn't find the row
        $this->assertNull($mapper->find(1));

        // mapper without the behaviour will find the row
        $this->assertNotNull($mapper->without('soft_delete')->find(1));
    }
}
