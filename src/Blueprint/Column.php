<?php
declare(strict_types=1);

namespace Sirius\Orm\Blueprint;

use Sirius\Orm\CodeGenerator\Observer\ColumnObserver;

/**
 * Class used for defining columns for the mapper
 * It contains specifications for the ORM but also for the database
 * so, theoretically, can be used to generate migrations
 */
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
    const TYPE_BOOLEAN = 'bool';

    protected $name;

    /**
     * Previous name of the column
     * Usable by DB migration builders
     * @var string
     */
    protected $previousName;

    /**
     * This should be used in case a column does not have the same name as
     * the name of the attribute in the entity
     * @var string
     */
    protected $attributeName;

    /**
     * @var string
     */
    protected $attributeCast;

    /**
     * @var string
     */
    protected $attributeType;

    /**
     * Indexed column, usable by DB migration builder
     * @var bool
     */
    protected $index = false;

    /**
     * Unique index column, usable by DB migration builder
     * @var bool
     */
    protected $unique = false;

    /**
     * Set column as auto incremented, usable by DB migration builder
     * @var bool
     */
    protected $autoIncrement = false;

    /**
     * For columns of type number
     * @var bool
     */
    protected $unsigned = false;

    /**
     * Default column value, usable by DB migration builder
     * @var mixed
     */
    protected $default;

    /**
     * The name of the column after which this is positioned,
     * usable by DB migration builder
     * @var string
     */
    protected $after;

    /**
     * Type of the column (integer, date, string etc)
     * @var string
     */
    protected $type;

    /**
     * For decimal type columns
     * @var int
     */
    protected $digits = 14;

    /**
     * For decimal type columns
     * @var int
     */
    protected $precision = 2;

    /**
     * For varchar type columns
     * @var int
     */
    protected $length = 255;

    /**
     * Is the column nullable?
     * @var bool
     */
    protected $nullable = false;

    /**
     * @var ColumnObserver
     */
    protected $observer;

    public static function make(string $name = null)
    {
        return (new static)->setName($name);
    }

    public static function varchar(string $name, $length = 255)
    {
        return static::make($name)
                     ->setType(static::TYPE_VARCHAR)
                     ->setLength($length);
    }

    public static function bool(string $name)
    {
        return static::make($name)
                     ->setType(static::TYPE_BOOLEAN);
    }

    public static function string(string $name)
    {
        return static::make($name)
                     ->setType(static::TYPE_TEXT);
    }

    public static function datetime(string $name)
    {
        return static::make($name)
                     ->setType(static::TYPE_DATETIME);
    }

    public static function date(string $name)
    {
        return static::make($name)
                     ->setType(static::TYPE_DATE);
    }

    public static function timestamp(string $name)
    {
        return static::make($name)
                     ->setType(static::TYPE_TIMESTAMP);
    }

    public static function json(string $name)
    {
        return static::make($name)
                     ->setType(static::TYPE_JSON);
    }

    public static function float(string $name)
    {
        return static::make($name)
                     ->setType(static::TYPE_FLOAT);
    }

    public static function integer(string $name, $unsigned = false)
    {
        return static::make($name)
                     ->setType(static::TYPE_INTEGER)
                     ->setUnsigned($unsigned);
    }

    public static function tinyInteger(string $name, $unsigned = false)
    {
        return static::make($name)
                     ->setType(static::TYPE_TINY_INTEGER)
                     ->setUnsigned($unsigned);
    }

    public static function smallInteger(string $name, $unsigned = false)
    {
        return static::make($name)
                     ->setType(static::TYPE_SMALL_INTEGER)
                     ->setUnsigned($unsigned);
    }

    public static function bigInteger(string $name, $unsigned = false)
    {
        return static::make($name)
                     ->setType(static::TYPE_BIG_INTEGER)
                     ->setUnsigned($unsigned);
    }

    public static function decimal(string $name, int $digits, int $precision)
    {
        return static::make($name)
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

    public function getObservers(): array
    {
        $observer = $this->getObserver()->with($this);

        return [
            $this->mapper->getName() . '_mapper_config' => [$observer],
            $this->mapper->getName() . '_base_entity'   => [$observer],
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Column
    {
        $this->name = $name;

        return $this;
    }

    public function getPreviousName(): ?string
    {
        return $this->previousName;
    }

    public function setPreviousName($previousName): Column
    {
        $this->previousName = $previousName;

        return $this;
    }

    public function getAttributeName(): ?string
    {
        return $this->attributeName;
    }

    public function setAttributeName(string $attributeName): Column
    {
        $this->attributeName = $attributeName;

        return $this;
    }

    public function getAttributeCast(): ?string
    {
        return $this->attributeCast;
    }

    /**
     * Set the cast type (string/integer/decimal:2/etc) of the attribute.
     * If not provided it is inferred from the column type
     */
    public function setAttributeCast(string $attributeCast): Column
    {
        $this->attributeCast = $attributeCast;

        return $this;
    }

    public function getAttributeType(): ?string
    {
        return $this->attributeType;
    }

    /**
     * Set the type of the attribute (int/float/string etc) for the entity.
     * If not provided it is inferred from the column type
     */
    public function setAttributeType(string $attributeType): Column
    {
        $this->attributeType = $attributeType;

        return $this;
    }

    public function getIndex(): bool
    {
        return $this->index;
    }

    public function setIndex(bool $index): Column
    {
        $this->index = $index;

        return $this;
    }

    public function getUnique(): bool
    {
        return $this->unique;
    }

    public function setUnique(bool $unique): Column
    {
        $this->unique = $unique;

        return $this;
    }

    public function getAutoIncrement(): bool
    {
        return $this->autoIncrement;
    }

    public function setAutoIncrement(bool $autoIncrement): Column
    {
        $this->autoIncrement = $autoIncrement;

        return $this;
    }

    public function getUnsigned(): bool
    {
        return $this->unsigned;
    }

    public function setUnsigned(bool $unsigned): Column
    {
        $this->unsigned = $unsigned;

        return $this;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    public function getAfter(): ?string
    {
        return $this->after;
    }

    public function setAfter(string $after): Column
    {
        $this->after = $after;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): Column
    {
        $this->type = $type;

        return $this;
    }

    public function getDigits(): int
    {
        return $this->digits;
    }

    public function setDigits(int $digits): Column
    {
        $this->digits = $digits;

        return $this;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }

    public function setPrecision(int $precision): Column
    {
        $this->precision = $precision;
        $this->setAttributeCast('decimal:' . $precision);

        return $this;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function setLength(int $length): Column
    {
        $this->length = $length;

        return $this;
    }

    public function getNullable(): bool
    {
        return $this->nullable;
    }

    public function setNullable(bool $nullable): Column
    {
        $this->nullable = $nullable;

        return $this;
    }

    /**
     * @return ColumnObserver
     */
    public function getObserver(): ColumnObserver
    {
        return $this->observer ?: new ColumnObserver();
    }

    /**
     * @param ColumnObserver $observer
     *
     * @return Column
     */
    public function setObserver(ColumnObserver $observer): Column
    {
        $this->observer = $observer;

        return $this;
    }
}
