<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests;

use Sirius\Orm\CastingManager;
use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Tests\Generated\Entity\Product;

class CastingManagerTest extends BaseTestCase
{

    /**
     * @var CastingManager
     */
    protected $cm;

    public function setUp(): void
    {
        parent::setUp();
        $this->cm = $this->orm->getCastingManager();
    }

    public function test_custom_cast()
    {
        $this->cm->register('short_text', function ($value, $limit = 100) {
            return substr($value, 0, $limit);
        });

        $this->assertEquals('abc', $this->cm->cast('short_text', 'abcdef', 3));
    }

    public function test_bool()
    {
        $this->assertFalse($this->cm->cast('bool', ''));
        $this->assertFalse($this->cm->cast('bool', 0));
        $this->assertFalse($this->cm->cast('bool', '0'));
    }

    public function test_json()
    {
        $this->assertSame([], $this->cm->cast('json', '')->getArrayCopy());
        $this->assertSame(['ab' => 2], $this->cm->cast('json', '{"ab":2}')->getArrayCopy());
        $this->assertSame(['ab' => 2], $this->cm->cast('json', ['ab' => 2])->getArrayCopy());
        $this->assertSame(['ab' => 2], $this->cm->cast('json', new \ArrayObject(['ab' => 2]))->getArrayCopy());
    }

    public function test_json_for_db()
    {
        $this->assertSame(null, $this->cm->cast('json_for_db', []));
        $this->assertSame('{"ab":2}', $this->cm->cast('json_for_db', '{"ab":2}'));
        $this->assertSame('{"ab":2}', $this->cm->cast('json_for_db', ['ab' => 2]));
        $this->assertSame('{"ab":2}', $this->cm->cast('json_for_db', new \ArrayObject(['ab' => 2])));
    }

    public function test_cast_array()
    {

        $result = $this->cm->castArray([
            'price'  => '10',
            'active' => '1',
        ], [
            'price'  => 'float',
            'active' => 'bool',
        ]);

        $this->assertSame([
            'price'  => 10.0,
            'active' => true
        ], $result);
    }

    public function test_cast_array_for_db()
    {

        $result = $this->cm->castArrayForDb([
            'price'  => 10.0,
            'active' => true
        ], [
            'price'  => 'float',
            'active' => 'bool',
        ]);

        $this->assertSame([
            'price'  => 10.0,
            'active' => 1
        ], $result);
    }

    public function test_cast_product_entity()
    {
        $result = $this->cm->cast('entity_from_products', [
            'id'    => 1,
            'value' => 10,
        ]);

        $this->assertInstanceOf(Product::class, $result);
    }

    public function test_cast_product_collection()
    {
        /** @var Collection $result */
        $result = $this->cm->cast('collection_of_products', [
            [
                'id'    => 1,
                'value' => 10,
            ],
            [
                'id'    => 2,
                'value' => 20,
            ]
        ]);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertInstanceOf(Product::class, $result->get(0));
        $this->assertEquals(1, $result->get(0)->id);
        $this->assertEquals(2, $result->get(1)->id);
    }
}
