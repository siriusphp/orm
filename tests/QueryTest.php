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

        $this->mapper = $this->orm->get('products');
    }

    public function test_find()
    {
        $this->insertRow('content', [
            'content_type' => 'product',
            'title'   => 'Product 1'
        ]);
        $entity = $this->mapper->find(1);
        $this->assertSame('Product 1', $entity->title);

        $this->assertNull($this->mapper->find(2));
    }

    public function test_query_get()
    {
        $this->insertRows('content', ['content_type', 'title'], [
            ['product', 'Product 1'],
            ['product', 'Product 2'],
            ['product', 'Product 3'],
            ['product', 'Product 4'],
        ]);

        $result = $this->mapper->newQuery()
                               ->where('title', 'Product 2', '>=')
                               ->get();

        $this->assertEquals(3, count($result));
    }

    public function test_query_paginate()
    {
        $this->insertRows('content', ['content_type', 'title'], [
            ['product', 'Product 1'],
            ['product', 'Product 2'],
            ['product', 'Product 3'],
            ['product', 'Product 4'],
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
                               ->where('title', 'Product 5', '>')
                               ->paginate(3, 2);

        $this->assertEquals(0, $result->getTotalCount());
        $this->assertEquals(0, $result->getPageStart());
        $this->assertEquals(0, $result->getPageEnd());
    }
}