<?php
declare(strict_types=1);

namespace Sirius\Orm\Tests\DbTests\Mysql\Behaviour;

class SoftDeleteTest extends \Sirius\Orm\Tests\DbTests\Base\Behaviour\SoftDeleteTest
{
    protected $dbEngine = 'mysql';
}
