<?php
error_reporting(E_ALL);
$autoloader = __DIR__ . '/../vendor/autoload.php';
if ( ! file_exists($autoloader)) {
    echo "Composer autoloader not found: $autoloader" . PHP_EOL;
    echo "Please issue 'composer install' and try again." . PHP_EOL;
    exit(1);
}
require $autoloader;

require_once ('resources/definitions.php');

foreach (['sqlite_schema_loaded', 'mysql_schema_loaded', 'mappers_generated'] as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        unlink($path);
    }
}
