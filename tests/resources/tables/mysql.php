<?php

return [
    "CREATE TABLE `products` (
        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
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
    )"
];