<?php

return [
    "DROP TABLE IF EXISTS content",
    "DROP TABLE IF EXISTS content_products",
    "DROP TABLE IF EXISTS categories",
    "DROP TABLE IF EXISTS images",
//    "DROP TABLE IF EXISTS image_relations",
    "DROP TABLE IF EXISTS products_tags",
    "DROP TABLE IF EXISTS tags",
    "CREATE TABLE `content` (
        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `created_at` DATETIME NULL DEFAULT NULL,
        `updated_at` DATETIME NULL DEFAULT NULL,
        `deleted_at` DATETIME NULL DEFAULT NULL,
        `content_type` VARCHAR(50) NULL DEFAULT NULL,
        `title` TEXT NULL,
        `summary` TEXT NULL,
        `description` TEXT NULL,
        PRIMARY KEY (`id`)
    )",
    "CREATE TABLE `content_products` (
        `content_id` INT(10) UNSIGNED NOT NULL,
        `category_id` INT(10) UNSIGNED NULL DEFAULT '0',
        `featured_image_id` INT(10) UNSIGNED NULL DEFAULT '0',
        `sku` VARCHAR(50) NULL DEFAULT '0',
        `price` DECIMAL(10,4) NULL DEFAULT '0.0000',
        PRIMARY KEY (`id`)
    )",
    "CREATE TABLE `categories` (
        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `parent_id` INT(11) NULL DEFAULT NULL,
        `name` VARCHAR(255) NULL DEFAULT NULL,
	    `details` TEXT NULL,
        PRIMARY KEY (`id`)
    )",
    "CREATE TABLE `images` (
        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `content_id` INT(10) UNSIGNED NOT NULL,
        `content_type` VARCHAR(50) NOT NULL,
        `name` VARCHAR(255) NOT NULL,
        `folder` VARCHAR(255) NULL,
        PRIMARY KEY (`id`)
    )",
//    "CREATE TABLE `image_relations` (
//        `image_id` INT(10) UNSIGNED NULL DEFAULT NULL,
//        `content_id` INT(10) UNSIGNED NULL DEFAULT NULL,
//        `content_type` VARCHAR(50) NOT NULL,
//        `priority` INT(10) UNSIGNED NOT NULL DEFAULT '0'
//    )",
    "CREATE TABLE `products_tags` (
        `product_id` INT(10) UNSIGNED NOT NULL,
        `tag_id` INT(10) UNSIGNED NOT NULL,
        `created_at` TIMESTAMP NOT NULL,
        `position` SMALLINT UNSIGNED NULL DEFAULT '0',
        PRIMARY KEY (`product_id`, `tag_id`)
    )",
    "CREATE TABLE `tags` (
        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(50) NOT NULL,
        PRIMARY KEY (`id`)
    )"
];