<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Collection;

use Sirius\Orm\Tests\BaseTestCase;
use Sirius\Orm\Tests\Generated\Entity\EbayProduct;

class ClassMethodsEntityTest extends BaseTestCase
{

    public function test_getter_method_is_called()
    {
        /** @var EbayProduct $product */
        $product = $this->orm->get('ebay_products')->newEntity(['id' => 10, 'price' => 100]);

        $this->assertEquals(100, $product->getPrice());
    }

    public function test_setter_method_is_called()
    {
        /** @var EbayProduct $product */
        $product = $this->orm->get('ebay_products')->newEntity(['id' => 10, 'price' => 100]);

        $product->setPrice(200);
        $this->assertEquals(200, $product->getPrice());
    }
}
