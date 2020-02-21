<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\Relation;

use Sirius\Orm\Mapper;
use Sirius\Orm\Relation\RelationConfig;
use Sirius\Orm\Tests\BaseTestCase;

class OneToOneTest extends BaseTestCase
{

    /**
     * @var Mapper
     */
    protected $nativeMapper;
    /**
     * @var Mapper
     */
    protected $foreignMapper;

    public function setUp(): void
    {
        parent::setUp();
        $this->loadMappers();

        $this->foreignMapper = $this->orm->get('images');
        $this->nativeMapper  = $this->orm->get('products');
    }

    public function test_delete_with_cascade_true()
    {
        // reconfigure products-featured_image to use CASCADE
        $config                                                           = $this->getMapperConfig('products');
        $config->relations['featured_image'][RelationConfig::CASCADE]     = true;
        $config->relations['featured_image'][RelationConfig::FOREIGN_KEY] = 'id';
        $this->nativeMapper                                               = $this->orm->register('products', $config)->get('products');

        $this->insertRow('products', ['id' => 1, 'featured_image_id' => 2]);
        $this->insertRow('images', ['id' => 1, 'name' => 'img.jpg']);

        $product = $this->nativeMapper->find(1);
        $this->assertNotNull($product->get('featured_image'));
        $this->assertTrue($this->nativeMapper->delete($product, true));
        $this->assertRowDeleted('products', 'id', 1);
        $this->assertRowDeleted('images', 'id', 2);
    }

    public function test_delete_with_cascade_false()
    {
        $this->insertRow('products', ['id' => 1, 'featured_image_id' => 2]);
        $this->insertRow('images', ['id' => 1, 'name' => 'img.jpg']);

        $product = $this->nativeMapper->find(1);
        $product->get('featured_image')->set('name', 'image.png');

        $this->assertTrue($this->nativeMapper->delete($product, true));
        $this->assertRowDeleted('products', 'id', 1);
        $image = $this->foreignMapper->find(1);
        $this->assertEquals('image.png', $image->get('name'));
    }

    public function test_save_with_relations()
    {
        $this->insertRow('products', ['id' => 3, 'featured_image_id' => 3]);
        $this->insertRow('images', ['id' => 3, 'name' => 'img.jpg']);

        $product = $this->nativeMapper->find(3);
        $product->set('sku', 'abc');
        $product->get('featured_image')->set('name', 'image.png');

        $this->assertTrue($this->nativeMapper->save($product, ['featured_image']));

        $product = $this->nativeMapper->find(3);
        $this->assertEquals('abc', $product->get('sku'));
        $image = $this->foreignMapper->find(3);
        $this->assertEquals('image.png', $image->get('name'));
    }

    public function test_save_without_relations()
    {
        $this->insertRow('products', ['id' => 3, 'featured_image_id' => 3]);
        $this->insertRow('images', ['id' => 3, 'name' => 'img.jpg']);

        $product = $this->nativeMapper->find(3);
        $product->set('sku', 'abc');
        $product->get('featured_image')->set('name', 'image.png');

        $this->assertTrue($this->nativeMapper->save($product, false));

        $product = $this->nativeMapper->find(3);
        $this->assertEquals('abc', $product->get('sku'));
        $image = $this->foreignMapper->find(3);
        $this->assertEquals('img.jpg', $image->get('name'));
    }

    public function test_join_with() {
        $query = $this->nativeMapper->newQuery()
                                    ->joinWith('featured_image');

        // the featured_image is not a real one-to-one relation
        $expectedStatement = <<<SQL
SELECT
    products.*
FROM
    products
    INNER JOIN (
    SELECT
        images.*
    FROM
        images
    ) AS featured_image ON products.id = images.id
SQL;

        $this->assertSameStatement($expectedStatement, $query->getStatement());
    }
}