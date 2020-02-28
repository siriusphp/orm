<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests;

use PHPUnit\Framework\TestCase;
use Sirius\Orm\CastingManager;

class CastingManagerTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
        $this->cm = new CastingManager();
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
}