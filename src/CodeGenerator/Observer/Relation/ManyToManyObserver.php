<?php
declare(strict_types=1);

namespace Sirius\Orm\CodeGenerator\Observer\Relation;

use Nette\PhpGenerator\ClassType;
use Sirius\Orm\Blueprint\Mapper;
use Sirius\Orm\Contract\Relation\ToManyInterface;
use Sirius\Orm\Helpers\Str;
use Sirius\Orm\MapperConfig;

class ManyToManyObserver extends OneToManyObserver implements ToManyInterface
{
    public function observe(string $key, $object)
    {
        if ($key === $this->relation->getForeignMapper() . '_base_entity') {
            return $this->observeLinkedBaseEntity($object);
        }
        if ($key === $this->relation->getForeignMapper() . '_mapper_config') {
            return $this->observeLinkedMapperConfig($object);
        }

        return parent::observe($key, $object);
    }

    protected function observeLinkedBaseEntity(ClassType $class)
    {
        $pivotColumns = $this->relation->getPivotColumns();
        if (empty($pivotColumns)) {
            return $class;
        }

        foreach ($pivotColumns as $column) {
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

    private function observeLinkedMapperConfig(array $config)
    {
        $config[MapperConfig::PIVOT_ATTRIBUTES] = array_merge(
            $config[MapperConfig::PIVOT_ATTRIBUTES] ?? [],
            array_values($this->relation->getPivotColumns() ?? [])
        );

        return $config;
    }
}
