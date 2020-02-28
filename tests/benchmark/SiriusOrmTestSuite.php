<?php


use Sirius\Orm\ConnectionLocator;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\Orm;
use Sirius\Orm\Relation\RelationConfig;

require_once __DIR__ . '/AbstractTestSuite.php';

/**
 * This test suite just demonstrates the baseline performance without any kind of ORM
 * or even any other kind of slightest abstraction.
 */
class SiriusOrmTestSuite extends AbstractTestSuite
{

    /**
     * @var Orm
     */
    private $orm;

    function initialize()
    {
        $loader = require_once __DIR__ . "/../../vendor/autoload.php";
        $loader->add('', __DIR__ . '/../../src');

        $this->con = \Sirius\Orm\Connection::new('sqlite::memory:');
        $this->orm = new Orm(ConnectionLocator::new($this->con));

        $this->initTables();

        $this->orm->register('products', Mapper::make($this->orm, MapperConfig::fromArray([
            MapperConfig::TABLE     => 'products',
            MapperConfig::COLUMNS   => ['id', 'name', 'sku', 'price', 'category_id'],
            MapperConfig::RELATIONS => [
                'images'   => [
                    RelationConfig::FOREIGN_MAPPER => 'images',
                    RelationConfig::TYPE           => RelationConfig::TYPE_ONE_TO_MANY,
                    RelationConfig::FOREIGN_KEY    => 'imageable_id',
                    RelationConfig::FOREIGN_GUARDS => ['imageable_type' => 'products']
                ],
                'category' => [
                    RelationConfig::FOREIGN_MAPPER => 'categories',
                    RelationConfig::TYPE           => RelationConfig::TYPE_MANY_TO_ONE
                ],
                'tags'     => [
                    RelationConfig::FOREIGN_MAPPER => 'tags',
                    RelationConfig::TYPE           => RelationConfig::TYPE_MANY_TO_MANY
                ]
            ]
        ])));

        $this->orm->register('categories', Mapper::make($this->orm, MapperConfig::fromArray([
            MapperConfig::TABLE   => 'categories',
            MapperConfig::COLUMNS => ['id', 'name'],

        ])));

        $this->orm->register('tags', Mapper::make($this->orm, MapperConfig::fromArray([
            MapperConfig::TABLE   => 'tags',
            MapperConfig::COLUMNS => ['id', 'name'],

        ])));

        $this->orm->register('images', Mapper::make($this->orm, MapperConfig::fromArray([
            MapperConfig::TABLE   => 'images',
            MapperConfig::COLUMNS => ['id', 'path', 'imageable_id', 'imageable_type'],

        ])));
    }

    function clearCache()
    {
    }

    function beginTransaction()
    {
        $this->transaction = $this->con->beginTransaction();
    }

    function commit()
    {
        $this->con->commit();
    }


    function insert($i)
    {
        $product = $this->orm->get('products')->newEntity([
            'name'     => 'Product #' . $i,
            'sku'      => 'SKU #' . $i,
            'price'    => sqrt(1000 + $i * 100),
            'category' => [
                'name' => 'Category #c' . $i
            ],
            'images'   => [
                ['path' => 'image_' . $i . '.jpg']
            ],
            'tags'     => [
                ['name' => 'Tag #t1_' . $i],
                ['name' => 'Tag #t2_' . $i]
            ]
        ]);

        $this->orm->save('products', $product);

        $this->products[] = $product->id;

        return $product;
    }

    public function test_insert()
    {
        $product = $this->insert(0);
        $product = $this->orm->find('products', $product->id);
        $this->assertNotNull($product, 'Product not found');
        $this->assertNotNull($product->category_id, 'Category was not associated with the product');
        $this->assertNotNull($product->images[0]->path, 'Image not present');
        $this->assertNotNull($product->tags[0]->name, 'Tag not present');
    }

    function prepare_update()
    {
        $this->product = $this->insert(0);
        $this->product = $this->orm->find('products', 1, ['category', 'images', 'tags']);
    }

    function update($i)
    {
        $this->product->name            = 'New product name ' . $i;
        $this->product->category->name  = 'New category name ' . $i;
        $this->product->images[0]->path = 'new_path_' . $i . '.jpg';
        $this->product->tags[0]->name   = 'New tag name ' . $i;
        $this->orm->save('products', $this->product);
    }

    function test_update()
    {

        $this->product->name            = 'New product name';
        $this->product->category->name  = 'New category name';
        $this->product->images[0]->path = 'new_path.jpg';
        $this->product->tags[0]->name = 'New tag name';
        $this->orm->save('products', $this->product);
        $product = $this->orm->find('products', 1, ['category', 'tags', 'images']);

        $this->assertEquals('New product name', $product->name);
        $this->assertEquals('New category name', $product->category->name);
        $this->assertEquals('new_path.jpg', $product->images[0]->path);

        // order not preserved for some reason
        $this->assertEquals('New tag name', $product->tags[1]->name);
        $this->assertEquals('Tag #t2_0', $product->tags[0]->name);
    }

    function find($i)
    {
        $this->orm->find('products', 1);
    }

    function test_find()
    {
        $product = $this->orm->find('products', 1);
        $lastRun = self::NB_TEST - 1;
        $this->assertEquals('New product name ' . $lastRun, $product->name); // changed by "update"
    }

    function complexQuery($i)
    {
        $this->orm->select('products')
                  ->join('INNER', 'categories', 'categories.id = products.category_id')
                  ->where('products.id', 50, '>')
                  ->where('categories.id', 300, '<')
                  ->count();
    }

    function test_complexQuery()
    {
        $this->assertEquals(249, $this->orm->select('products')
                                           ->join('INNER', 'categories', 'categories.id = products.category_id')
                                           ->where('products.id', 50, '>')
                                           ->where('categories.id', 300, '<')
                                           ->count());
    }

    function relations($i)
    {
        $products = $this->orm->select('products')
                              ->load('category', 'tags', 'images')
                              ->where('price', 50, '>')
                              ->limit(10)
                              ->get();
        foreach ($products as $product) {

        }
    }

    function test_relations()
    {
        $product = $this->orm->get('products')->find(1);
        $lastRun = self::NB_TEST - 1;
        $this->assertEquals('New product name ' . $lastRun, $product->name);
        $this->assertEquals('New category name ' . $lastRun, $product->category->name);
        $this->assertEquals('new_path_' . $lastRun . '.jpg', $product->images[0]->path);
        $this->assertEquals('New tag name ' . $lastRun, $product->tags[1]->name);
        $this->assertEquals('Tag #t2_0', $product->tags[0]->name);
    }
}