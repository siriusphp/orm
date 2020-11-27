<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests;

use Sirius\Orm\Mapper;

class MapperTest extends BaseTestCase
{
    /**
     * @var Mapper
     */
    protected $mapper;

    public function setUp(): void
    {
        parent::setUp();

        $this->mapper = $this->orm->get('products');
    }

    public function test_new_entity()
    {
        $product = $this->mapper->newEntity([
            'category_id'       => '10',
            'featured_image_id' => '20',
            'sku'               => 'sku 1',
            'price'             => '100.343'
        ]);

        $this->assertEquals(100.34, $product->value);
        $this->assertEquals(10, $product->category_id);
        $this->assertEquals(20, $product->featured_image_id);
    }

    public function test_exception_thrown_for_invalid_relation()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->mapper->addRelation('wrong', new \stdClass());
    }

    public function test_exception_thrown_retrieving_a_non_declared_relation()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->mapper->getRelation('wrong');
    }
}
