<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests;

use Sirius\Orm\CastingManager;
use Sirius\Orm\Entity\GenericEntityHydrator;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\Tests\Entity\ProductEntity;

class MapperTest extends BaseTestCase
{
    /**
     * @var Mapper
     */
    protected $mapper;

    public function setUp(): void
    {
        parent::setUp();

        $mapperConfig = MapperConfig::fromArray([
            MapperConfig::TABLE                => 'products',
            MapperConfig::ENTITY_CLASS         => ProductEntity::class,
            MapperConfig::TABLE_ALIAS          => 'p',
            MapperConfig::COLUMNS              => ['id', 'category_id', 'featured_image_id', 'sku', 'price'],
            MapperConfig::COLUMN_ATTRIBUTE_MAP => ['price' => 'value'],
            MapperConfig::CASTS                => ['value' => 'decimal:2']
        ]);
        $mapperConfig->setEntityHydrator(new GenericEntityHydrator($mapperConfig, CastingManager::getInstance()));
        $this->mapper = Mapper::make($this->connectionLocator, $mapperConfig);

    }

    public function test_new_entity()
    {
        $product = $this->mapper->newEntity([
            'category_id'       => '10',
            'featured_image_id' => '20',
            'sku'               => 'sku 1',
            'price'             => '100.343'
        ]);

        $this->assertEquals(100.34, $product->value);
        $this->assertEquals(10, $product->category_id);
        $this->assertEquals(20, $product->featured_image_id);
    }
}
