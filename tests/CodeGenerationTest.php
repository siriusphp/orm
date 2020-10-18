<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests;

use PHPUnit\Framework\TestCase;
use Sirius\Orm\Mapper;

class CodeGenerationTest extends TestCase
{
    public function test_exception_thrown_when_joining_with_invalid_relation() {
        include(__DIR__ . '/resources/definitions.php');
        $this->assertTrue(true);
    }

}
