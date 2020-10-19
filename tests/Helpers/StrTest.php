<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests;

use PHPUnit\Framework\TestCase;
use Sirius\Orm\Helpers\Str;

class StrTest extends TestCase
{

    public function test_underscore()
    {
        $this->assertEquals('abc_def', Str::underscore('abc Def'));
        $this->assertEquals('abc_def', Str::underscore('abc-def'));
        $this->assertEquals('abc_def', Str::underscore('abcDef'));
    }

    public function test_method_name()
    {
        $this->assertEquals('getAttributeName', Str::methodName('attribute_name', 'get'));
    }

    public function test_variable_name()
    {
        $this->assertEquals('primaryKey', Str::variableName('primary_key'));
    }
}
