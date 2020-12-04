<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests;

use Sirius\Orm\Mapper;

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

    public function test_exception_thrown_when_joining_with_invalid_relation()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->mapper->newQuery()->joinWith('undefined');
    }

    public function test_applyFilters() {
        $query = $this->mapper->newQuery()
            ->applyFilters([
                'id' => '1,2,3',
                'price' => [
                    'lte' => 10,
                    'greater_or_equal' => 5
                ],
                'name' => [
                    'starts_with' => 'abc',
                    'contains' => 'fgh',
                    'ends_with' => 'xyz',
                ]
            ]);
        $statement = <<<SQL
SELECT
    products.*
FROM
    tbl_products as products 
WHERE 
((id IN (:__1__, :__2__, :__3__) 
AND price <= :__4__ 
AND price >= :__5__ 
AND name LIKE :__6__ 
AND name LIKE :__7__ 
AND name LIKE :__8__))  
AND deleted_on IS NULL    
SQL;

        $this->assertSameStatement($statement, $query->getStatement());
        $this->assertEquals([
            '__1__' => [1, 2],
            '__2__' => [2, 2],
            '__3__' => [3, 2],
            '__4__' => [10, 1],
            '__5__' => [5, 1],
            '__6__' => ['abc%', 2],
            '__7__' => ['%fgh%', 2],
            '__8__' => ['%xyz', 2],
        ], $query->getBindValues());
    }

    public function test_joining_multiple_times_with_the_same_relation()
    {
        $query = $this->mapper->newQuery()
                              ->joinWith('category')
                              ->joinWith('category');

        $statement = <<<SQL
SELECT
    products.*
FROM
    tbl_products as products 
INNER JOIN (
    SELECT categories.* FROM categories
    ) AS category ON products.category_id = category.id
WHERE deleted_on IS NULL    
SQL;

        $this->assertSameStatement($statement, $query->getStatement());
    }

    public function test_find()
    {
        $this->insertRow('tbl_products', [
            'sku' => 'sku_1'
        ]);
        $entity = $this->mapper->find(1);
        $this->assertSame('sku_1', $entity->sku);

        $this->assertNull($this->mapper->find(2));
    }

    public function test_query_get()
    {
        $this->insertRows('tbl_products', ['price', 'sku'], [
            [10, 'sku_1'],
            [20, 'sku_2'],
            [30, 'sku_3'],
            [40, 'sku_4'],
        ]);

        $result = $this->mapper->newQuery()
                               ->where('sku', 'sku_2', '>=')
                               ->get();

        $this->assertEquals(3, count($result));
    }

    public function test_where_field_in_relation()
    {
        $this->insertRow('categories', [
            'id'   => 1,
            'name' => 'category'
        ]);
        $this->insertRow('tbl_products', [
            'category_id' => 1,
            'sku'         => 'sku_1'
        ]);

        $result = $this->mapper->newQuery()
                               ->where('category.name', 'category')
                               ->get();

        $this->assertEquals(1, count($result));
    }

    public function test_chunk()
    {
        $this->insertRows('tbl_products', ['price', 'sku'], [
            [2, 'sku_ 1'],
            [3, 'sku_ 2'],
            [4, 'sku_ 3'],
            [5, 'sku_ 4'],
            [6, 'sku_ 5'],
            [7, 'sku_ 6'],
        ]);

        $found  = 0;
        $result = $this->mapper->newQuery()
                               ->chunk(2, function ($entity) use (&$found) {
                                   $found += 1;
                               }, 2);

        $this->assertEquals(4, $found);
    }

    public function test_chunk_no_limit()
    {
        $this->insertRows('tbl_products', ['price', 'sku'], [
            [2, 'sku_ 1'],
            [3, 'sku_ 2'],
            [4, 'sku_ 3'],
            [5, 'sku_ 4'],
            [6, 'sku_ 5'],
            [100, 'sku_7'],
        ]);

        $found = 0;

        $query = $this->mapper->newQuery()->where('price', 100, '<');
        $query->chunk(2, function ($entity) use (&$found) {
            $found += 1;
        });

        $this->assertEquals(5, $found);
    }

    public function test_query_paginate()
    {
        $this->insertRows('tbl_products', ['price', 'sku'], [
            [2, 'sku_ 1'],
            [3, 'sku_ 2'],
            [4, 'sku_ 3'],
            [5, 'sku_ 4'],
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
                               ->where('sku', 'sku_2', '>')
                               ->paginate(3, 2);

        $this->assertEquals(0, $result->getTotalCount());
        $this->assertEquals(0, $result->getPageStart());
        $this->assertEquals(0, $result->getPageEnd());
    }
}
