<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests;

use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;

class QueryTest extends BaseTestCase
{
    /**
     * @var Mapper
     */
    protected $mapper;

    public function setUp(): void
    {
        parent::setUp();

        $this->mapper = Mapper::make($this->orm, MapperConfig::fromArray([
            MapperConfig::TABLE       => 'products',
            MapperConfig::TABLE_ALIAS => 'p',
            MapperConfig::COLUMNS     => ['id', 'category_id', 'featured_image_id', 'sku', 'price']
        ]));
    }

    public function test_find()
    {
        $this->insertRow('products', [
            'sku'   => 'abc',
            'price' => 10.5
        ]);
        $entity = $this->mapper->find(1);
        $this->assertSame('abc', $entity->get('sku'));

        $this->assertNull($this->mapper->find(2));
    }

    public function test_query_get()
    {
        $this->insertRows('products', ['sku', 'price'], [
            ['sku-1', 10],
            ['sku-2', 20],
            ['sku-3', 30],
            ['sku-4', 40],
        ]);

        $result = $this->mapper->newQuery()
                               ->where('price', '20', '>=')
                               ->get();

        $this->assertEquals(3, count($result));
    }

    public function test_query_paginate()
    {
        $this->insertRows('products', ['sku', 'price'], [
            ['sku-1', 10],
            ['sku-2', 20],
            ['sku-3', 30],
            ['sku-4', 40],
        ]);

        $result = $this->mapper->newQuery()
                               ->paginate(3, 2);

        $this->assertEquals(1, count($result));
        $this->assertEquals(2, $result->getCurrentPage());
        $this->assertEquals(4, $result->getPageStart());
        $this->assertEquals(4, $result->getPageStart());
        $this->assertEquals(4, $result->getPageEnd());
        $this->assertEquals(2, $result->getTotalPages());
        $this->assertEquals(3, $result->getPerPage());
        $this->assertEquals(4, $result->getTotalCount());


        $result = $this->mapper->newQuery()
                               ->where('price', 50, '>')
                               ->paginate(3, 2);

        $this->assertEquals(0, $result->getTotalCount());
        $this->assertEquals(0, $result->getPageStart());
        $this->assertEquals(0, $result->getPageEnd());
    }

    public function test_guards()
    {
        $this->insertRows('products', ['category_id', 'sku', 'price'], [
            [1, 'sku-1', 10],
            [2, 'sku-2', 20],
            [2, 'sku-3', 30],
            [1, 'sku-4', 40],
        ]);

        $result = $this->mapper->newQuery()
                               ->setGuards(['category_id' => 1])
                               ->get();

        $this->assertEquals(2, count($result));
    }
}