<?php

use Doctrine\DBAL\Schema\Schema;

$schema = new Schema();

$tables = [];

// content table
// holds all type of contents (like `posts` in Wordpress)
$t = $schema->createTable('content');
$t->addColumn("id", "integer", ["unsigned" => true])->setAutoincrement(true);
$t->addColumn("created_at", "datetime")->setNotnull(false);
$t->addColumn("updated_at", "datetime")->setNotnull(false);
$t->addColumn("deleted_at", "datetime")->setNotnull(false);
$t->addColumn("content_type", "string", ["length" => 32]);
$t->addColumn("title", "text")->setNotnull(false);
$t->addColumn("summary", "text")->setNotnull(false);
$t->addColumn("description", "text")->setNotnull(false);
$t->setPrimaryKey(["id"]);
$t->addIndex(["content_type"]);

$tables[$t->getName()] = $t;

// contents_products
// holds product related fields
$t = $schema->createTable('content_products');
$t->addColumn("content_id", "integer", ["unsigned" => true])->setAutoincrement(true);
$t->addColumn("category_id", "integer", ["unsigned" => true])->setNotnull(false);
$t->addColumn("featured_image_id", "integer", ["unsigned" => true])->setNotnull(false);
$t->addColumn("sku", "string", ["length" => 64])->setDefault('');
$t->addColumn("price", "decimal", ["precision" => 12, 'scale' => 4])->setDefault(0);
$t->addUniqueIndex(['content_id']);

$tables[$t->getName()] = $t;

// categories
// for testing parent-child relations
$t = $schema->createTable('categories');
$t->addColumn("id", "integer", ["unsigned" => true])->setAutoincrement(true);
$t->addColumn("parent_id", "integer", ["unsigned" => true])->setNotnull(false);
$t->addColumn("name", "string", ["length" => 255]);
$t->addColumn("details", "json")->setNotnull(false);
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
$t->addUniqueIndex(['content_id', 'content_type']);

$tables[$t->getName()] = $t;

// tags
// for testing many-to-many relations
$t = $schema->createTable('tags');
$t->addColumn("id", "integer", ["unsigned" => true])->setAutoincrement(true);
$t->addColumn("name", "string", ["length" => 255]);
$t->setPrimaryKey(["id"]);

$tables[$t->getName()] = $t;

$t = $schema->createTable('products_tags');
$t->addColumn("product_id", "integer", ["unsigned" => true]);
$t->addColumn("tag_id", "integer", ["unsigned" => true]);
$t->addColumn("position", "integer", ["unsigned" => true])->setDefault(0);
$t->addUniqueIndex(["product_id", "tag_id"]);

$tables[$t->getName()] = $t;

return $schema;
