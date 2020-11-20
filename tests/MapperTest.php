<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests;

use Sirius\Orm\CastingManager;
use Sirius\Orm\Entity\GenericHydrator;
use Sirius\Orm\DynamicMapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\Tests\Entity\ProductEntity;

class MapperTest extends BaseTestCase
{
    /**
     * @var DynamicMapper
     */
    protected $mapper;

    public function setUp(): void
    {
        parent::setUp();

        $mapperConfig = MapperConfig::fromArray([
            MapperConfig::TABLE                => 'products',
            MapperConfig::TABLE                => 'products',
            MapperConfig::ENTITY_CLASS         => ProductEntity::class,
            MapperConfig::TABLE_ALIAS          => 'p',
            MapperConfig::COLUMNS              => ['id', 'category_id', 'featured_image_id', 'sku', 'price'],
            MapperConfig::COLUMN_ATTRIBUTE_MAP => ['price' => 'value'],
            MapperConfig::CASTS                => ['value' => 'decimal:2']
        ]);
        $this->mapper = DynamicMapper::make($this->orm, $mapperConfig);
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
