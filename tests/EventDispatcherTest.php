<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests;

use League\Event\HasEventName;
use Sirius\Orm\Tests\Generated\Mapper\ProductMapper;

class EventDispatcherTest extends BaseTestCase
{
    /**
     * @var ProductMapper
     */
    protected $mapper;

    public function setUp(): void
    {
        parent::setUp();

        $this->mapper        = $this->orm->get('products');
        $this->eventsCounter = [];

        $events = ['query', 'new_entity', 'saving', 'saved', 'deleting', 'deleted'];
        foreach ($events as $event) {
            $this->eventDispatcher->subscribeTo('products.' . $event, [$this, 'handleEvent']);
        }
    }

    public function test_events_are_applied()
    {
        $mapper = $this->mapper;

        $mapper->newQuery();
        $this->assertEventListenerRanFor('products.query');

        $entity = $mapper->newEntity(['sku' => 'sku_1']);
        $this->assertEventListenerRanFor('products.new_entity');

        $mapper->save($entity);
        $this->assertEventListenerRanFor('products.saving');
        $this->assertEventListenerRanFor('products.saved');

        $mapper->delete($entity);
        $this->assertEventListenerRanFor('products.deleting');
        $this->assertEventListenerRanFor('products.deleted');
    }

    public function test_mapper_without_events()
    {
        $mapper = $this->mapper->without('events');

        $mapper->newQuery();
        $this->assertEventListenerHasNotRanFor('products.query');

        $entity = $mapper->newEntity(['sku' => 'sku_1']);
        $this->assertEventListenerHasNotRanFor('products.new_entity');

        $mapper->save($entity);
        $this->assertEventListenerHasNotRanFor('products.saving');
        $this->assertEventListenerHasNotRanFor('products.saved');

        $mapper->delete($entity);
        $this->assertEventListenerHasNotRanFor('products.deleting');
        $this->assertEventListenerHasNotRanFor('products.deleted');
    }

    public function handleEvent(HasEventName $event)
    {
        if ( ! isset($this->eventsCounter[$event->eventName()])) {
            $this->eventsCounter[$event->eventName()] = 0;
        }
        $this->eventsCounter[$event->eventName()]++;
    }

    protected function assertEventListenerRanFor($eventName)
    {
        $this->assertEquals(1,
            $this->eventsCounter[$eventName],
            sprintf('Listener was not executed for event %s', $eventName));
    }

    protected function assertEventListenerHasNotRanFor($eventName)
    {
        $this->assertEquals(0,
            $this->eventsCounter[$eventName] ?? 0,
            sprintf('Listener was executed for event %s', $eventName));
    }
}
