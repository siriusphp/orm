<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\DbTests\Postgres\Relation;

class ManyToManyTest extends \Sirius\Orm\Tests\DbTests\Base\Relation\ManyToManyTest
{
    protected $dbEngine = 'postgres';
}
