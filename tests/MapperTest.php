<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests;

use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Mapper;
use Sirius\Orm\Tests\Generated\Entity\Category;
use Sirius\Orm\Tests\Generated\Entity\EbayProduct;
use Sirius\Orm\Tests\Generated\Entity\Image;
use Sirius\Orm\Tests\Generated\Entity\Product;
use Sirius\Orm\Tests\Generated\Entity\ProductLanguage;
use Sirius\Orm\Tests\Generated\Entity\Tag;

class MapperTest extends BaseTestCase
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

    public function test_new_entity_with_relations()
    {
        /** @var Product $product */
        $product = $this->mapper->newEntity([
            'category_id'       => '10',
            'featured_image_id' => '20',
            'sku'               => 'sku 1',
            'price'             => '100.343',
            'ebay'              => [
                'id'    => 1,
                'price' => 120
            ],
            'category'          => [
                'id'   => 1,
                'name' => 'Category'
            ],
            'images'            => [
                ['id' => 1, 'path' => 'a.jpg'],
                ['id' => 2, 'path' => 'b.jpg'],
            ],
            'tags'              => [
                ['id' => 1, 'position_in_product' => 2, 'name' => 'A'],
                ['id' => 2, 'position_in_product' => 1, 'name' => 'B'],
            ]
        ]);

        $this->assertInstanceOf(EbayProduct::class, $product->ebay);
        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertInstanceOf(Collection::class, $product->images);
        $this->assertInstanceOf(Image::class, $product->images->get(0));
        $this->assertInstanceOf(Collection::class, $product->tags);
        $this->assertInstanceOf(Tag::class, $product->tags->get(0));
        $this->assertEquals(2, $product->tags->get(0)->position_in_product);

        // even though we haven't provided a `languages` key in the above array
        // we want to be able to add elements to the array collection via the array
        $this->assertInstanceOf(Collection::class, $product->languages);
        $product->languages->add(['id' => 1, 'name' => 'Category']);
        $this->assertInstanceOf(ProductLanguage::class, $product->languages->get(0));
    }

    public function test_patching()
    {
        /** @var Product $product */
        $product = $this->mapper->newEntity([
            'category_id'       => '10',
            'featured_image_id' => '20',
            'sku'               => 'sku 1',
            'price'             => '100.343',
            'ebay'              => [
                'id'    => 1,
                'price' => 120
            ],
            'category'          => [
                'id'   => 1,
                'name' => 'Category'
            ],
            'images'            => [
                ['id' => 1, 'path' => 'a.jpg'],
                ['id' => 2, 'path' => 'b.jpg'],
            ],
            'tags'              => [
                ['id' => 1, 'position_in_product' => 2, 'name' => 'A'],
                ['id' => 2, 'position_in_product' => 1, 'name' => 'B'],
            ]
        ]);

        $product = $this->mapper->patch($product, [
            'sku'      => 'sku 2',
            'ebay'     => ['price' => 100],
            'category' => ['name' => 'New Category'],
            'images' => [
                ['id' => 1, 'path' => 'c.jpg'],
                ['id' => null, 'path' => 'd.jpg'],
            ],
            'tags'              => [
                ['id' => null, 'position_in_product' => 1, 'name' => 'C'],
                ['id' => 2, 'position_in_product' => 2, 'name' => 'B'],
            ],
            'languages'              => [
                ['lang' => 'en', 'title' => 'Product title'],
                ['lang' => 'fr', 'title' => 'Nome de produit'],
            ]
        ]);

        $this->assertEquals(100, $product->ebay->getPrice());
        $this->assertEquals('New Category', $product->category->getName());

        $this->assertEquals(2, $product->images->count());
        $this->assertEquals(1, $product->images->get(0)->id);
        $this->assertEquals(null, $product->images->get(1)->id);

        $this->assertEquals(2, $product->tags->count());
        $this->assertEquals([2, null], $product->tags->pluck('id'));
        $this->assertEquals([2, 1], $product->tags->pluck('position_in_product'));

        $this->assertEquals(2, $product->languages->count());
    }

    public function test_exception_thrown_for_invalid_relation()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->mapper->addRelation('wrong', new \stdClass());
    }

    public function test_exception_thrown_retrieving_a_non_declared_relation()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->mapper->getRelation('wrong');
    }
}
