<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests;

use PHPUnit\Framework\TestCase;

class CodeGenerationTest extends TestCase
{
    public function test_exception_thrown_when_joining_with_invalid_relation()
    {
        include(__DIR__ . '/resources/definitions.php');

        foreach (['Mapper', 'Entity'] as $folder) {
            $path          = __DIR__ . '/Generated/' . $folder;
            $snapshotsPath = __DIR__ . '/resources/snapshots/Generated/' . $folder;
            $files         = scandir($path);
            foreach ($files as $file) {
                $classFile    = $path . '/' . $file;
                $snapshotFile = $snapshotsPath . '/' . $file;
                if (is_file($classFile)) {
                    $snapshot  = str_replace("\r", "", file_get_contents($snapshotFile));
                    $generated = str_replace("\r", "", file_get_contents($classFile));
                    $this->assertEquals($snapshot, $generated);
                }
            }
        }
    }

}
