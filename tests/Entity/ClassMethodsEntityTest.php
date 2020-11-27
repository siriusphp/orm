<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Collection;

use Sirius\Orm\Tests\BaseTestCase;
use Sirius\Orm\Tests\Generated\Entity\Product;

class ClassMethodsEntityTest extends BaseTestCase
{

    public function test_getter_method_is_called()
    {
        /** @var Product $product */
        $product = $this->orm->get('ebay')->newEntity(['id' => 10, 'price' => 100]);

        $this->assertEquals(90, $product->discounted_price);
    }

    public function test_setter_method_is_called()
    {
        /** @var Product $product */
        $product = $this->orm->get('products')->newEntity(['id' => 10, 'price' => 100]);

        $product->discounted_price = 180;
        $this->assertEquals(200, $product->value);
    }
}
