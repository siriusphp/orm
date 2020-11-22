<?php

use Doctrine\DBAL\Schema\Schema;

$schema = new Schema();

$tables = [];

$t = $schema->createTable('tbl_languages');
$t->addColumn("id", "integer", ["unsigned" => true])->setAutoincrement(true);
$t->addColumn("content_type", "string", ["length" => 32]);
$t->addColumn("content_id", "integer", ["unsigned" => true])->setNotnull(false);
$t->addColumn("lang", "string", ["length" => 5]);
$t->addColumn("title", "string", ["length" => 255]);
$t->addColumn("slug", "string", ["length" => 255]);
$t->addColumn("description", "text")->setNotnull(false);
$t->setPrimaryKey(["id"]);

$tables[$t->getName()] = $t;

$t = $schema->createTable('tbl_products');
$t->addColumn("id", "integer", ["unsigned" => true])->setAutoincrement(true);
$t->addColumn("created_on", "datetime")->setNotnull(false);
$t->addColumn("updated_on", "datetime")->setNotnull(false);
$t->addColumn("deleted_on", "datetime")->setNotnull(false);
$t->addColumn("category_id", "integer", ["unsigned" => true])->setNotnull(false);
$t->addColumn("sku", "string", ["length" => 255]);
$t->addColumn("price", "decimal", ["precision" => 14, 'scale' => 2])->setDefault(0);
$t->addColumn("attributes", "json")->setNotnull(false);
$t->setPrimaryKey(["id"]);

$tables[$t->getName()] = $t;

// for testing one-to-one relations
$t = $schema->createTable('tbl_ebay_products');
$t->addColumn("id", "integer", ["unsigned" => true])->setAutoincrement(true);
$t->addColumn("product_id", "integer", ["unsigned" => true])->setNotnull(false);
$t->addColumn("price", "decimal", ["precision" => 14, 'scale' => 2])->setDefault(0);
$t->addColumn("is_active", "boolean")->setDefault(true);
$t->setPrimaryKey(["id"]);

$tables[$t->getName()] = $t;

// images
// for testing one-to-many relations
$t = $schema->createTable('images');
$t->addColumn("id", "integer", ["unsigned" => true])->setAutoincrement(true);
$t->addColumn("content_id", "integer", ["unsigned" => true]);
$t->addColumn("content_type", "string", ["length" => 64]);
$t->addColumn("name", "string", ["length" => 255]);
$t->addColumn("folder", "string", ["length" => 255])->setNotnull(false);
$t->setPrimaryKey(["id"]);

$tables[$t->getName()] = $t;

// categories
// for testing parent-child relations
$t = $schema->createTable('categories');
$t->addColumn("id", "integer", ["unsigned" => true])->setAutoincrement(true);
$t->addColumn("parent_id", "integer", ["unsigned" => true])->setNotnull(false);
$t->addColumn("name", "string", ["length" => 255]);
$t->addColumn("position", "integer", ['unsigned' => true])->setDefault(0);
$t->setPrimaryKey(["id"]);

$tables[$t->getName()] = $t;

// tags
// for testing many-to-many relations
$t = $schema->createTable('tags');
$t->addColumn("id", "integer", ["unsigned" => true])->setAutoincrement(true);
$t->addColumn("name", "string", ["length" => 255]);
$t->setPrimaryKey(["id"]);

$tables[$t->getName()] = $t;

$t = $schema->createTable('tbl_links_to_tags');
$t->addColumn("tagable_type", "string", ["length" => 255]);
$t->addColumn("tagable_id", "integer", ["unsigned" => true]);
$t->addColumn("tag_id", "integer", ["unsigned" => true]);
$t->addColumn("position", "integer", ["unsigned" => true])->setDefault(0);
$t->addUniqueIndex(["tagable_type", "tagable_id", "tag_id"]);

$tables[$t->getName()] = $t;

return $schema;
