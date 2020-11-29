<?php
declare(strict_types=1);

namespace Sirius\Orm\CodeGenerator\Observer\Relation;

use Nette\PhpGenerator\ClassType;
use Sirius\Orm\Blueprint\Mapper;
use Sirius\Orm\Helpers\Str;

class ManyToManyObserver extends OneToManyObserver
{
    public function observe(string $key, $object)
    {
        if ($key === $this->relation->getForeignMapper() . '_base_entity') {
            return $this->observeLinkedBaseEntity($object);
        }

        return parent::observe($key, $object);
    }

    protected function observeLinkedBaseEntity(ClassType $class)
    {
        $throughColumns = $this->relation->getThroughColumns();
        if (empty($throughColumns)) {
            return $class;
        }

        foreach ($throughColumns as $column) {
            $comment = 'unsed only for relations with ' . $this->relation->getMapper()->getName();
            if ($this->relation->getMapper()->getEntityStyle() === Mapper::ENTITY_STYLE_PROPERTIES) {
                $class->addComment(sprintf('@property mixed $%s - %s', $column, $comment));
            } else {
                $setter = $class->addMethod(Str::methodName($column, 'set'));
                $setter->setVisibility(ClassType::VISIBILITY_PUBLIC);
                $setter->addParameter('value');
                $setter->addBody('$this->set(\'' . $column . '\', $value);');
                $setter->setComment($comment);

                $getter = $class->addMethod(Str::methodName($column, 'get'));
                $getter->setVisibility(ClassType::VISIBILITY_PUBLIC);
                $getter->addBody('return $this->get(\'' . $column . '\');');
                $setter->setComment($comment);
            }
        }

        return $class;
    }
}
