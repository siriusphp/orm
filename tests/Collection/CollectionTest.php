<?php

declare(strict_types=1);

namespace Sirius\Orm\Tests\Collection;

use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Entity\GenericHydrator;
use Sirius\Orm\Tests\BaseTestCase;
use Sirius\Orm\Tests\Generated\Entity\Product;

class CollectionTest extends BaseTestCase
{

    /**
     * @var Collection
     */
    protected $collection;

    public function setUp(): void
    {
        parent::setUp();
        $hydrator = new GenericHydrator($this->orm->getCastingManager());
        $hydrator->setMapperConfig($this->orm->get('products')->getConfig());
        $this->collection = new Collection([], $hydrator);
    }

    public function test_add_using_entity()
    {
        $entity = $this->orm->get('products')->newEntity(['id' => 10]);
        $this->collection->add($entity);
        $this->assertTrue($this->collection->contains($entity));
    }

    public function test_excetion_thrown_while_adding_invalid_elements()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->collection->add(new \stdClass());
    }

    public function test_add_using_array()
    {
        $this->collection->add(['id' => 10]);
        $this->assertInstanceOf(Product::class, $this->collection->get(0));
        $this->assertEquals(10, $this->collection->get(0)->id);
        $this->assertEquals(1, count($this->collection->getChanges()['added']));
    }

    public function test_adding_the_same_entity_twice()
    {
        $this->collection->add(['id' => 10]);
        $this->collection->add(['id' => 10]);
        $this->assertEquals(1, $this->collection->count());
        $this->assertEquals(1, count($this->collection->getChanges()['added']));
    }

    public function test_remove_using_array()
    {
        $this->collection->add(['id' => 10]);
        $this->assertEquals(1, $this->collection->count());

        $this->assertTrue($this->collection->removeElement(['id' => 10]));
        $this->assertEquals(0, $this->collection->count());
        $this->assertEquals(1, count($this->collection->getChanges()['removed']));
    }

    public function test_remove_using_entity()
    {
        $this->collection->add(['id' => 10]);
        $this->assertEquals(1, $this->collection->count());

        $this->assertTrue($this->collection->removeElement($this->collection->get(0)));
        $this->assertEquals(0, $this->collection->count());
        $this->assertEquals(1, count($this->collection->getChanges()['removed']));
    }

    public function test_remove_missing_element()
    {
        $entity = $this->orm->get('products')->newEntity(['id' => 10]);
        $this->assertTrue($this->collection->removeElement($entity));
        $this->assertEmpty($this->collection->getChanges()['removed']);
    }

    public function test_pluck_single()
    {
        $this->collection->add(['id' => 10, 'name' => 'product A']);
        $this->collection->add(['id' => 20, 'name' => 'product B']);

        $this->assertEquals([10, 20], $this->collection->pluck('id'));
    }

    public function test_pluck_multiple()
    {
        $this->collection->add(['id' => 10, 'name' => 'product A']);
        $this->collection->add(['id' => 20, 'name' => 'product B']);

        $this->assertEquals([
            ['id' => 10, 'name' => 'product A'],
            ['id' => 20, 'name' => 'product B']
        ], $this->collection->pluck(['id', 'name']));
    }

    public function test_reduce()
    {
        $this->collection->add(['id' => 10, 'name' => 'product A']);
        $this->collection->add(['id' => 20, 'name' => 'product B']);

        $this->assertEquals(40, $this->collection->reduce(function ($acc, $p) {
            return $acc + $p->id;
        }, 10));
    }

    public function test_find_by_primary_key()
    {
        $this->collection->add(['id' => 10, 'name' => 'product A']);
        $this->assertNotNull($this->collection->findByPk(10));
        $this->assertNull($this->collection->findByPk(20));
    }

    public function test_remove()
    {
        $this->collection->add(['id' => 10, 'name' => 'product A']);
        $this->assertNotNull($this->collection->remove(0));
        $this->assertEquals(1, count($this->collection->getChanges()['removed']));

    }
}
