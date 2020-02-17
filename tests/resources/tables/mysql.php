<?php

return [
    "CREATE TABLE `products` (
        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `created_at` DATETIME NULL,
        `updated_at` DATETIME NULL,
        `deleted_at` DATETIME NULL,
        `category_id` INT(10) UNSIGNED NULL DEFAULT '0',
        `featured_image_id` INT(10) UNSIGNED NULL DEFAULT '0',
        `sku` VARCHAR(50) NULL DEFAULT '0',
        `price` DECIMAL(10,4) NULL DEFAULT '0.0000',
        PRIMARY KEY (`id`)
    )",
    "CREATE TABLE `categories` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `parent_id` UNSIGNED INT(11) NULL DEFAULT '0',
        `name` VARCHAR(200) NULL DEFAULT '0',
        PRIMARY KEY (`id`)
    )",
    "CREATE TABLE `images` (
        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(255) NOT NULL DEFAULT '0',
        `folder` VARCHAR(255) NULL DEFAULT '0',
        PRIMARY KEY (`id`)
    )",
    "CREATE TABLE `image_relations` (
        `image_id` INT(10) UNSIGNED NULL DEFAULT NULL,
        `content_id` INT(10) UNSIGNED NULL DEFAULT NULL,
        `content_type` VARCHAR(50) NOT NULL,
        `priority` INT(10) UNSIGNED NOT NULL DEFAULT '0'
    )",
    "CREATE TABLE `products_tags` (
        `product_id` INT(10) UNSIGNED NOT NULL,
        `tag_id` INT(10) UNSIGNED NOT NULL,
        `created_at` TIMESTAMP NOT NULL,
        PRIMARY KEY (`product_id`, `tag_id`)
    )",
    "CREATE TABLE `tags` (
        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(50) NOT NULL,
        PRIMARY KEY (`id`)
    )"
];