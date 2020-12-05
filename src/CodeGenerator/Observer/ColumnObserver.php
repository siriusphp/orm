<?php
declare(strict_types=1);

namespace Sirius\Orm\CodeGenerator\Observer;

use DateTime;
use Nette\PhpGenerator\ClassType;
use Sirius\Orm\Blueprint\Column;
use Sirius\Orm\Blueprint\Mapper;
use Sirius\Orm\Blueprint\Relation;
use Sirius\Orm\Helpers\Str;

class ColumnObserver extends Base
{
    /**
     * @var Column
     */
    protected $column;

    public function with(Column $column)
    {
        $clone         = clone($this);
        $clone->column = $column;

        return $clone;
    }

    public function observe(string $key, $object)
    {
        if ($key == $this->column->getMapper()->getName() . '_mapper_config') {
            return $this->observeMapperConfig($object);
        }
        if ($key == $this->column->getMapper()->getName() . '_base_entity') {
            return $this->observeBaseEntity($object);
        }

        return $object;
    }

    public function __toString()
    {
        return sprintf('Observer for column %s of mapper %s', $this->column->getName(), $this->column->getMapper()->getName());
    }

    public function observeMapperConfig(array $config)
    {
        $config['columns'][]                       = $this->column->getName();
        $config['casts'][$this->column->getName()] = $this->getAttributeCastForConfig();
        if ($this->column->getAttributeName() && $this->column->getAttributeName() != $this->column->getName()) {
            $config['columnAttributeMap'][$this->column->getName()] = $this->column->getAttributeName();
        }

        return $config;
    }

    public function observeBaseEntity(ClassType $class)
    {
        $name = $this->column->getAttributeName() ?: $this->column->getName();
        $type = $this->getAttributeTypeForEntityClass();

        if (class_exists($type)) {
            /** @scrutinizer ignore-deprecated */ $class->getNamespace()->addUse($type);
            $type = basename($type);
        }

        if (($body = $this->getCastMethodBody($this->column->getType()))) {
            $cast = $class->addMethod(Str::methodName($name . ' Attribute', 'cast'));
            $cast->setVisibility(ClassType::VISIBILITY_PROTECTED);
            $cast->addParameter('value');
            $cast->addBody($body);
        }

        if ($this->column->getMapper()->getEntityStyle() === Mapper::ENTITY_STYLE_PROPERTIES) {
            $type .= $this->column->getNullable() ? '|null' : '';
            $class->addComment(sprintf('@property %s $%s', $type, $name));
        } else {
            $setter = $class->addMethod(Str::methodName($name, 'set'));
            $setter->setVisibility(ClassType::VISIBILITY_PUBLIC);
            $setter->addParameter('value')
                    ->setType($type)
                   ->setNullable($this->attributeIsNullable());
            $setter->addBody('$this->set(\'' . $name . '\', $value);');

            $getter = $class->addMethod(Str::methodName($name, 'get'));
            $getter->setVisibility(ClassType::VISIBILITY_PUBLIC);
            $getter->addBody('return $this->get(\'' . $name . '\');');
            $getter->setReturnType($type)
                   ->setReturnNullable($this->attributeIsNullable());
        }

        return $class;
    }

    private function getCastMethodBody(string $type)
    {
        switch ($type) {
            case Column::TYPE_FLOAT:
                return 'return $value === null ? $value : floatval($value);';

            case Column::TYPE_JSON:
                return 'return $value === null ? $value : (is_array($value) ? $value : \json_decode($value, true));';

            case Column::TYPE_INTEGER:
            case Column::TYPE_BIG_INTEGER:
            case Column::TYPE_SMALL_INTEGER:
            case Column::TYPE_TINY_INTEGER:
                return 'return $value === null ? $value : intval($value);';

            case Column::TYPE_DECIMAL:
                return 'return $value === null ? $value : round((float)$value, ' . $this->column->getPrecision() . ');';

            case Column::TYPE_DATETIME:
                return 'return !$value ? null : (($value instanceof DateTime) ? $value : new DateTime($value));';

            default:
                return null;
        }
    }

    private function getAttributeTypeForEntityClass()
    {
        if ($this->column->getAttributeType()) {
            return $this->column->getAttributeType();
        }
        $map = $this->getColumnTypeCastMap();

        return $map[$this->column->getType()] ?: 'mixed';
    }

    private function getAttributeCastForConfig()
    {
        if ($this->column->getAttributeCast()) {
            return $this->column->getAttributeCast();
        }
        $map = $this->getColumnTypeCastMap();

        return $map[$this->column->getType()] ?: 'mixed';
    }

    private function getColumnTypeCastMap()
    {
        return [
            Column::TYPE_BOOLEAN       => 'bool',
            Column::TYPE_VARCHAR       => 'string',
            Column::TYPE_TEXT          => 'string',
            Column::TYPE_JSON          => 'array',
            Column::TYPE_INTEGER       => 'int',
            Column::TYPE_BIG_INTEGER   => 'int',
            Column::TYPE_SMALL_INTEGER => 'int',
            Column::TYPE_TINY_INTEGER  => 'int',
            Column::TYPE_FLOAT         => 'float',
            Column::TYPE_DECIMAL       => 'float',
            Column::TYPE_DATE          => DateTime::class,
            Column::TYPE_DATETIME      => DateTime::class,
            Column::TYPE_TIMESTAMP     => DateTime::class,
        ];
    }

    /**
     * @return bool
     */
    protected function attributeIsNullable(): bool
    {
        if ($this->column->getNullable() || $this->column->getAutoIncrement()) {
            return true;
        }

        $relations = $this->column->getMapper()->getRelations();
        /** @var Relation $relation */
        foreach ($relations as $relation) {
            if ($relation->getNativeKey() === $this->column->getName()) {
                return true;
            }
        }

        return false;
    }
}
