<?php
declare(strict_types=1);

namespace Sirius\Orm\Contract;

use Sirius\Orm\Action\Delete;
use Sirius\Orm\Action\Save;
use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\Orm;
use Sirius\Orm\Query;

interface MapperInterface
{
    public function setOrm(Orm $orm);

    public function newQuery(array $options = []): Query;

    public function newEntity(array $data, string $state): EntityInterface;

    public function save(EntityInterface $entity, $withRelations = false): bool;

    public function newSaveAction(EntityInterface $entity): Save;

    public function delete(EntityInterface $entity): bool;

    public function newDeleteAction(EntityInterface $entity): Delete;
}
