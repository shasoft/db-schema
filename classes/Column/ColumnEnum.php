<?php

namespace Shasoft\DbSchema\Column;

use Shasoft\DbSchema\Command\Enum;
use Shasoft\DbSchema\Command\Type;
use Shasoft\DbSchema\Column\Column;
use Shasoft\DbSchema\Tests\EnumInt;
use Shasoft\DbSchema\Command\Seeder;
use Shasoft\DbSchema\Command\Comment;
use Shasoft\DbSchema\Command\DbSchemaType;
use Shasoft\DbSchema\Command\MaxValue;
use Shasoft\DbSchema\Command\MinValue;
use Shasoft\DbSchema\Command\Variable;
use Shasoft\DbSchema\Command\MaxLength;
use Shasoft\DbSchema\Command\MinLength;
use Shasoft\DbSchema\Tests\EnumDefault;
use Shasoft\DbSchema\State\StateCommands;
use Shasoft\DbSchema\Column\ColumnInteger;
use Shasoft\DbSchema\Command\DefaultValue;
use Shasoft\DbSchema\Command\ConversionInput;
use Shasoft\DbSchema\Command\ConversionOutput;
use Shasoft\DbSchema\Tests\EnumString;

// Перечисление
class ColumnEnum extends Column
{
    // Список значений
    protected array $values = [];
    // Конструктор
    public function __construct()
    {
        // Вызвать конструктор родителя
        parent::__construct();
        // Установить команды
        $this->setCommand(new Comment('Перечисление'));
        $this->setCommand(new DbSchemaType('Enum'), false);
        $this->setCommand(new DefaultValue());
        // Обязательные команды
        $this->addRequiredCommand([
            Enum::class
        ]);
    }
    // Событие изменения типа
    protected function onCommandEnum(Enum $enum): void
    {
        // Получить значения
        $items = $enum->items();
        // Тип указан?
        if ($enum->hasType()) {
            // Тип указан
            $maxLen = 0;
            $minValue = null;
            $maxValue = null;
            foreach ($items as $item) {
                if (is_string($item->value)) {
                    $maxLen = max($maxLen, strlen($item->value));
                } else {
                    if (is_null($maxValue)) {
                        $minValue = $item->value;
                        $maxValue = $minValue;
                    } else {
                        $minValue = min($minValue, $item->value);
                        $maxValue = max($maxValue, $item->value);
                    }
                }
            }
            if (is_null($maxValue)) {
                $this->setCommand(new MinLength(0), false);
                $this->setCommand(new MaxLength($maxLen), false);
                $this->setCommand(new DbSchemaType('String'), false);
            } else {
                $this->setCommand(new MinValue($minValue), false);
                $this->setCommand(new MaxValue($maxValue), false);
                $this->setCommand(new DbSchemaType('Integer'), false);
            }
        } else {
            // Тип не указан
            $maxLen = 0;
            foreach ($items as $item) {
                $maxLen = max($maxLen, strlen($item->name));
            }
            $this->setCommand(new MinLength(0), false);
            $this->setCommand(new Variable(false), false);
            $this->setCommand(new MaxLength($maxLen), false);
            $this->setCommand(new DbSchemaType('String'), false);
        }
        // Установить команду для конвертации
        $this->setCommand(new ConversionInput(self::class . '::input'), false);
        $this->setCommand(new ConversionOutput(self::class . '::output'), false);
        // Установить команду заполнитель
        $this->setCommand(new Seeder(self::class . '::value'));
    }
    // PHP=>БД
    public static function input($value, StateCommands $column): mixed
    {
        return $column->get(Enum::class)->input($value);
    }
    // БД=>PHP
    public static function output(mixed $value, StateCommands $column): mixed
    {
        return $column->get(Enum::class)->output($value);
    }
    // Получить случайное значение
    public static function value(StateCommands $column): mixed
    {
        //
        $enum = $column->get(Enum::class);
        return self::random(
            $enum->items(),
            $enum->hasType()
        );
    }
    // Получить случайное значение
    public static function random(array $items, string $hasType): mixed
    {
        if (empty($items)) {
            return null;
        }
        // Получить случайный элемент
        return $items[ColumnInteger::random(0, count($items) - 1)];
    }
};
