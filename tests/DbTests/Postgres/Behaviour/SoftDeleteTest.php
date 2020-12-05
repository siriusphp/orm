<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\DbTests\Postgres\Behaviour;

class SoftDeleteTest extends \Sirius\Orm\Tests\DbTests\Base\Behaviour\SoftDeleteTest
{
    protected $dbEngine = 'postgres';
}
