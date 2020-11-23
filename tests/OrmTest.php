<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests;

use Sirius\Orm\DynamicMapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\Relation\RelationConfig;
use Sirius\Orm\Tests\Generated\Mapper\ProductMapper;

class OrmTest extends BaseTestCase
{
    public function test_lazy_mapper_factory()
    {
        $this->orm->register('products', function () {
            return new ProductMapper($this->orm);
        });

        $this->assertTrue($this->orm->has('products'));
        $this->assertInstanceOf(ProductMapper::class, $this->orm->get('products'));
    }

    public function test_mapper_instance()
    {
        $this->assertInstanceOf(ProductMapper::class, $this->orm->get('products'));
    }

    public function test_exception_thrown_on_invalid_relation_type()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->orm->createRelation($this->orm->get('products'), 'unknown', [
            RelationConfig::FOREIGN_MAPPER => 'categories',
            RelationConfig::TYPE           => 'unknown'
        ]);
    }

    public function test_exception_thrown_on_invalid_mapper_instance()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->orm->register('products', new \stdClass());
    }

    public function test_exception_thrown_on_unknown_mapper()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->orm->get('pages');
    }

    public function test_exception_thrown_on_invalid_mapper_factory()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->orm->register('products', function () {
            return new \stdClass();
        });
        $this->orm->get('products');
    }
}
