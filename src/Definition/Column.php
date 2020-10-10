<?php
declare(strict_types=1);

namespace Sirius\Orm\Definition;

class Column extends Base
{
    use MapperAwareTrait;

    const TYPE_VARCHAR = 'varchar';
    const TYPE_FLOAT = 'float';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_INTEGER = 'integer';
    const TYPE_SMALL_INTEGER = 'small integer';
    const TYPE_TINY_INTEGER = 'tiny integer';
    const TYPE_BIG_INTEGER = 'big integer';
    const TYPE_DATE = 'date';
    const TYPE_DATETIME = 'datetime';
    const TYPE_TIMESTAMP = 'timestamp';
    const TYPE_TEXT = 'text';
    const TYPE_JSON = 'json';

    protected $name;

    protected $previousName;

    protected $attributeName;

    protected $attributeCast;

    protected $attributeType;

    protected $index = false;

    protected $unique = false;

    protected $autoIncrement = false;

    protected $unsigned = false;

    protected $default;

    protected $after;

    protected $type;

    protected $digits = 14;

    protected $precision = 2;

    protected $length = 255;

    protected $nullable = false;

    public static function varchar($length = 255)
    {
        return static::make()
                     ->setType(static::TYPE_VARCHAR)
                     ->setLength($length);
    }

    public static function string()
    {
        return static::make()
                     ->setType(static::TYPE_TEXT);
    }

    public static function datetime()
    {
        return static::make()
                     ->setType(static::TYPE_DATETIME);
    }

    public static function date()
    {
        return static::make()
                     ->setType(static::TYPE_DATE);
    }

    public static function timestamp()
    {
        return static::make()
                     ->setType(static::TYPE_TIMESTAMP);
    }

    public static function json()
    {
        return static::make()
                     ->setType(static::TYPE_JSON);
    }

    public static function float()
    {
        return static::make()
                     ->setType(static::TYPE_FLOAT);
    }

    public static function integer($unsigned = false)
    {
        return static::make()
                     ->setType(static::TYPE_INTEGER)
                     ->setUnsigned($unsigned);
    }

    public static function tinyInteger($unsigned = false)
    {
        return static::make()
                     ->setType(static::TYPE_TINY_INTEGER)
                     ->setUnsigned($unsigned);
    }

    public static function smallInteger($unsigned = false)
    {
        return static::make()
                     ->setType(static::TYPE_SMALL_INTEGER)
                     ->setUnsigned($unsigned);
    }

    public static function bigInteger($unsigned = false)
    {
        return static::make()
                     ->setType(static::TYPE_BIG_INTEGER)
                     ->setUnsigned($unsigned);
    }

    public static function decimal(int $digits, int $precision)
    {
        return static::make()
                     ->setType(static::TYPE_DECIMAL)
                     ->setDigits($digits)
                     ->setPrecision($precision);
    }


    public function getErrors(): array
    {
        $errors = [];

        if ( ! $this->name) {
            $errors[] = 'Column requires a name';
        }

        if ( ! $this->type) {
            $errors[] = 'Column requires a type';
        } elseif (($type = $this->getConstantByValue($this->type)) && substr($type, 0, 5) !== 'TYPE_') {
            $errors[] = sprintf('Column does not have a valid type (%s)', $this->type);
        }

        return $errors;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return Column
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPreviousName()
    {
        return $this->previousName;
    }

    /**
     * @param mixed $previousName
     *
     * @return Column
     */
    public function setPreviousName($previousName)
    {
        $this->previousName = $previousName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAttributeName()
    {
        return $this->attributeName;
    }

    /**
     * @param mixed $attributeName
     *
     * @return Column
     */
    public function setAttributeName($attributeName)
    {
        $this->attributeName = $attributeName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAttributeCast()
    {
        return $this->attributeCast;
    }

    /**
     * @param string $attributeCast
     *
     * @return Column
     */
    public function setAttributeCast(string $attributeCast): Column
    {
        $this->attributeCast = $attributeCast;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAttributeType()
    {
        return $this->attributeType;
    }

    /**
     * @param string $attributeType
     *
     * @return Column
     */
    public function setAttributeType(string $attributeType): Column
    {
        $this->attributeType = $attributeType;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIndex(): bool
    {
        return $this->index;
    }

    /**
     * @param bool $index
     *
     * @return Column
     */
    public function setIndex(bool $index): Column
    {
        $this->index = $index;

        return $this;
    }

    /**
     * @return bool
     */
    public function getUnique(): bool
    {
        return $this->unique;
    }

    /**
     * @param bool $unique
     *
     * @return Column
     */
    public function setUnique(bool $unique): Column
    {
        $this->unique = $unique;

        return $this;
    }

    /**
     * @return bool
     */
    public function getAutoIncrement(): bool
    {
        return $this->autoIncrement;
    }

    /**
     * @param bool $autoIncrement
     *
     * @return Column
     */
    public function setAutoIncrement(bool $autoIncrement): Column
    {
        $this->autoIncrement = $autoIncrement;

        return $this;
    }

    /**
     * @return bool
     */
    public function getUnsigned(): bool
    {
        return $this->unsigned;
    }

    /**
     * @param bool $unsigned
     *
     * @return Column
     */
    public function setUnsigned(bool $unsigned): Column
    {
        $this->unsigned = $unsigned;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param mixed $default
     *
     * @return Column
     */
    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAfter()
    {
        return $this->after;
    }

    /**
     * @param mixed $after
     *
     * @return Column
     */
    public function setAfter($after)
    {
        $this->after = $after;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     *
     * @return Column
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return int
     */
    public function getDigits(): int
    {
        return $this->digits;
    }

    /**
     * @param int $digits
     *
     * @return Column
     */
    public function setDigits(int $digits): Column
    {
        $this->digits = $digits;

        return $this;
    }

    /**
     * @return int
     */
    public function getPrecision(): int
    {
        return $this->precision;
    }

    /**
     * @param int $precision
     *
     * @return Column
     */
    public function setPrecision(int $precision): Column
    {
        $this->precision = $precision;

        return $this;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @param int $length
     *
     * @return Column
     */
    public function setLength(int $length): Column
    {
        $this->length = $length;

        return $this;
    }

    /**
     * @return bool
     */
    public function getNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * @param bool $nullable
     *
     * @return Column
     */
    public function setNullable(bool $nullable): Column
    {
        $this->nullable = $nullable;

        return $this;
    }


}
