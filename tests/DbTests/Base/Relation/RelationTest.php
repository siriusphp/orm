<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\DbTests\Base\Relation;

use Sirius\Orm\Entity\GenericEntity;
use Sirius\Orm\Entity\Tracker;
use Sirius\Orm\Mapper;
use Sirius\Orm\MapperConfig;
use Sirius\Orm\Relation\ManyToOne;
use Sirius\Orm\Relation\RelationConfig;
use Sirius\Orm\Tests\BaseTestCase;

class RelationTest extends BaseTestCase
{
    public function test_multi_column_primary_key()
    {
        $this->nativeMapper = Mapper::make($this->connectionLocator, MapperConfig::fromArray([
            MapperConfig::TABLE   => 'products',
            MapperConfig::COLUMNS => ['id', 'related_col_1', 'related_col_2']
        ]));

        $this->foreignMapper = Mapper::make($this->connectionLocator, MapperConfig::fromArray([
            MapperConfig::TABLE       => 'categories',
            MapperConfig::PRIMARY_KEY => ['col_1', 'col_2'],
            MapperConfig::COLUMNS     => ['col_1', 'col_2', 'name']
        ]));

        $relation = new ManyToOne('related', $this->nativeMapper, $this->foreignMapper);

        $this->assertSame(['related_col_1', 'related_col_2'], $relation->getOption(RelationConfig::NATIVE_KEY));

        $native1 = new GenericEntity(['related_col_1' => 10, 'related_col_2' => 10]);
        $native2 = new GenericEntity(['related_col_1' => 10, 'related_col_2' => 20]);

        $foreign1 = new GenericEntity(['col_1' => 10, 'col_2' => 10]);
        $foreign2 = new GenericEntity(['col_1' => 10, 'col_2' => 20]);

        $tracker = new Tracker([
            $native1->toArray(),
            $native2->toArray(),
        ]);

        $expectedStatement = <<<SQL
SELECT
    categories.*
FROM
    categories
WHERE
    (
        (
        col_1 = :__1__
        AND col_2 = :__2__
        )
        OR (
        col_1 = :__3__
        AND col_2 = :__4__
        )
    )
SQL;

        $this->assertSameStatement($expectedStatement, $relation->getQuery($tracker)->getStatement());
        $this->assertSame([
            '__1__' => [10, \PDO::PARAM_INT],
            '__2__' => [10, \PDO::PARAM_INT],
            '__3__' => [10, \PDO::PARAM_INT],
            '__4__' => [20, \PDO::PARAM_INT],
        ], $relation->getQuery($tracker)->getBindValues());
    }
}
