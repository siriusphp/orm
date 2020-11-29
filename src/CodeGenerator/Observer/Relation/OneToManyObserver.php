<?php
declare(strict_types=1);

namespace Sirius\Orm\CodeGenerator\Observer\Relation;

use Nette\PhpGenerator\ClassType;
use Sirius\Orm\Blueprint\Mapper;
use Sirius\Orm\Blueprint\Relation;
use Sirius\Orm\Blueprint\Relation\OneToMany;
use Sirius\Orm\CodeGenerator\Observer\Base;
use Sirius\Orm\Collection\Collection;
use Sirius\Orm\Contract\Relation\ToManyInterface;
use Sirius\Orm\Helpers\Inflector;
use Sirius\Orm\Helpers\Str;

class OneToManyObserver extends Base implements ToManyInterface
{

    /**
     * @var OneToMany
     */
    protected $relation;

    public function with(Relation $relation)
    {
        $clone           = clone($this);
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
        return sprintf('Observer for relation %s for mapper %s',
            $this->relation->getName(),
            $this->relation->getMapper()->getName()
        );
    }

    public function observeBaseEntity(ClassType $class)
    {
        $this->addAttributeToConstructor($class);

        $mapper        = $this->relation->getMapper();
        $name          = $this->relation->getName();
        $foreignMapper = $mapper->getOrm()->getMapper($this->relation->getForeignMapper());
        $type          = $foreignMapper->getEntityNamespace()
                         . '\\' . $foreignMapper->getEntityClass();

        $class->getNamespace()->addUse(Collection::class);
        $class->getNamespace()->addUse($type, null, $type);

        $adder = $class->addMethod(Str::methodName(Inflector::singularize($name), 'add'));
        $adder->setVisibility(ClassType::VISIBILITY_PUBLIC);
        $adder->addParameter('value')
              ->setType($type);
        $adder->addBody('
$this->attributes[\'' . $name . '\']->addElement($value);        
        ');

        if ($mapper->getEntityStyle() === Mapper::ENTITY_STYLE_PROPERTIES) {
            $class->addComment(sprintf('@property %s[]|Collection $%s', $type, $name));
        } else {
            $setter = $class->addMethod(Str::methodName($name, 'set'));
            $setter->setVisibility(ClassType::VISIBILITY_PUBLIC);
            $setter->addParameter('value')
                   ->setType('Collection');
            $setter->addBody('$this->set(\'' . $name . '\', $value);');

            $getter = $class->addMethod(Str::methodName($name, 'get'));
            $getter->setVisibility(ClassType::VISIBILITY_PUBLIC);
            $getter->addBody('return $this->get(\'' . $name . '\');');
            $getter->setReturnType('Collection')
                   ->setReturnNullable(true);
        }

        return $class;
    }

    private function addAttributeToConstructor(ClassType $class)
    {
        $name        = $this->relation->getName();

        $constructor = $class->getMethod('__construct');
        $constructor->addBody(rtrim('
// this is a fail-safe procedure that will be executed
// only when you use `new Entity()` instead of `$mapper->newEntity()`
// ALWAYS try to use `$mapper->newEntity()`
if (!isset($this->attributes[\'' . $name . '\'])) {
    $this->attributes[\'' . $name . '\'] = new Collection;
}        
        '));
    }
}
