<?php
declare(strict_types=1);

namespace Sirius\Orm\CodeGenerator\Observer\Relation;

use Nette\PhpGenerator\ClassType;
use Sirius\Orm\Blueprint\Mapper;
use Sirius\Orm\Blueprint\Relation;
use Sirius\Orm\Blueprint\Relation\ManyToMany;
use Sirius\Orm\Blueprint\Relation\ManyToOne;
use Sirius\Orm\CodeGenerator\Observer\Base;
use Sirius\Orm\Contract\Relation\ToOneInterface;
use Sirius\Orm\Helpers\Str;

class ManyToOneObserver extends Base implements ToOneInterface
{

    /**
     * @var ManyToOne
     */
    protected $relation;

    public function with(Relation $relation)
    {
        $clone         = clone($this);
        $clone->relation = $relation;

        return $clone;
    }

    public function observe(string $key, $object)
    {
        if ($key == $this->relation->getMapper()->getName() . '_base_entity') {
            return $this->observeBaseEntity($object);
        }

        return $object;
    }

    public function __toString()
    {
        return sprintf(
            'Observer for relation %s for mapper %s',
            $this->relation->getName(),
            $this->relation->getMapper()->getName()
        );
    }

    public function observeBaseEntity(ClassType $class)
    {
        $mapper        = $this->relation->getMapper();
        $name          = $this->relation->getName();
        $foreignMapper = $mapper->getOrm()->getMapper($this->relation->getForeignMapper());
        $type          = $foreignMapper->getEntityNamespace()
                         . '\\' . $foreignMapper->getEntityClass();

        /** @scrutinizer ignore-deprecated */ $class->getNamespace()->addUse($type, null, $type);

        $cast = $class->addMethod(Str::methodName($name . ' Attribute', 'cast'));
        $cast->setVisibility(ClassType::VISIBILITY_PROTECTED);
        $cast->addParameter('value');
        $cast->addBody('
if ($value === null) {
    return $value;
}        
        ');
        $cast->addBody(sprintf('return $value instanceOf %s ? $value : new %s((array) $value);', $type, $type));

        if ($mapper->getEntityStyle() === Mapper::ENTITY_STYLE_PROPERTIES) {
            $class->addComment(sprintf('@property %s|null $%s', $type, $name));
        } else {
            $setter = $class->addMethod(Str::methodName($name, 'set'));
            $setter->setVisibility(ClassType::VISIBILITY_PUBLIC);
            $setter->addParameter('value')
                   ->setType($type)
                   ->setNullable(true);
            $setter->addBody('$this->set(\'' . $name . '\', $value);');

            $getter = $class->addMethod(Str::methodName($name, 'get'));
            $getter->setVisibility(ClassType::VISIBILITY_PUBLIC);
            $getter->addBody('return $this->get(\'' . $name . '\');');
            $getter->setReturnType($type)
                   ->setReturnNullable(true);
        }

        return $class;
    }
}
