<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests;

use PHPUnit\Framework\TestCase;
use Sirius\Orm\Helpers\Arr;

class ArrTest extends TestCase
{

    public function test_children()
    {
        $arr = [
            'key_1.key_2'       => null,
            'key_1.key_3'       => null,
            'key_2.key_3'       => null,
            'key_1.key_2.key_3' => null
        ];

        $this->assertSame([
            'key_2'       => null,
            'key_3'       => null,
            'key_2.key_3' => null,
        ], Arr::getChildren($arr, 'key_1'));

        $this->assertSame([
            'key_3' => null,
        ], Arr::getChildren($arr, 'key_1.key_2'));
    }

    public function test_ensure_parents()
    {
        $arr = [
            'key_1.key_2.key_3' => null
        ];

        $this->assertSame([
            'key_1.key_2.key_3' => null,
            'key_1'             => null,
            'key_1.key_2'       => null
        ], Arr::ensureParents($arr));
    }
}