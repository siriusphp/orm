<?php
declare(strict_types=1);

namespace Sirius\Orm\CodeGenerator\Observer\Relation;

use Sirius\Orm\Contract\Relation\ToOneInterface;

class OneToOneObserver extends ManyToOneObserver implements ToOneInterface
{
}
