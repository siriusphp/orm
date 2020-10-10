<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\Action;

use Sirius\Orm\CastingManager;
use Sirius\Orm\Entity\GenericEntityHydrator;
use Sirius\Orm\Entity\StateEnum;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\Tests\BaseTestCase;
use Sirius\Orm\Tests\Behaviour\ThrowExceptionBehaviour;

class UpdateTest extends BaseTestCase
{
    public function test_entity_is_updated()
    {
        $mapper = $this->orm->get('products');

        $product = $mapper->newEntity(['title' => 'Product 1']);
        $mapper->save($product);

        // reload after insert
        $product              = $mapper->find($product->id);
        $product->description = 'Description product 1';
        $mapper->save($product);

        // reload after save
        $product = $mapper->find($product->id);
        $this->assertEquals('Description product 1', $product->description);
        $this->assertEquals(StateEnum::SYNCHRONIZED, $product->getState());
    }

    public function test_entity_is_reverted_on_exception()
    {
        // create a clone so the ORM is not affected
        $mapper = $this->orm->get('products')->without();
        $mapper->use(new ThrowExceptionBehaviour());

        $this->insertRow('content', ['content_type' => 'product', 'title' => 'Product 1']);

        $product        = $mapper->find(1);
        $product->title = 'Product 2';

        $this->expectException(\Exception::class);
        $mapper->save($product);
        $this->assertEquals(StateEnum::CHANGED, $product->getState());
    }

    public function test_column_is_mapped_to_attribute()
    {
        $mapper = Mapper::make($this->connectionLocator, MapperConfig::fromArray([
            MapperConfig::TABLE                => 'content',
            MapperConfig::COLUMNS              => ['id', 'content_type', 'title', 'description', 'summary'],
            MapperConfig::COLUMN_ATTRIBUTE_MAP => ['summary' => 'excerpt'],
            MapperConfig::GUARDS               => ['content_type' => 'product']
        ]));
        $mapper->getConfig()->setEntityHydrator(new GenericEntityHydrator($mapper->getConfig(), CastingManager::getInstance()));

        $this->insertRow('content', ['content_type' => 'product', 'title' => 'Product 1', 'summary' => 'Excerpt']);

        $product = $mapper->find(1);
        $this->assertEquals('Excerpt', $product->excerpt);

        $product->excerpt = 'New excerpt';

        $mapper->save($product);
        $product = $mapper->find(1);
        $this->assertEquals('New excerpt', $product->excerpt);
    }
}
