<?php
declare(strict_types=1);

namespace Sirius\Orm\Collection;

use Doctrine\Common\Collections\AbstractLazyCollection;
use Sirius\Orm\Entity\EntityInterface;
use Sirius\Orm\Loaders\LazyLoader;

class LazyCollectionForRelation extends AbstractLazyCollection
{
    protected function __construct(LazyLoader $loader, EntityInterface $entity)
    {
        $this->loader = $loader;
        $this->entity = $entity;
    }

    /**
     * @inheritDoc
     */
    protected function doInitialize()
    {
        $this->collection = $this->loader->getForEntity($this->entity);
    }
}
