<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests;

use PHPUnit\Framework\TestCase;
use Sirius\Orm\Blueprint\Orm;

class CodeGenerationTest extends TestCase
{
    public function test_generated_code_matches_snapshot()
    {
        /** @var Orm $orm */
        $orm = include(__DIR__ . '/resources/definitions.php');

//        $observers = $orm->getObservers();
//        foreach ($observers as $k => $list) {
//            echo $k, '---------', PHP_EOL;
//            foreach ($list as $observer) {
//                echo $observer, PHP_EOL;
//            }
//        }
//        die();

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
