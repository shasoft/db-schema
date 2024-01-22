<?php

namespace Shasoft\DbSchema\Column;

use Shasoft\DbSchema\Column\Column;
use Shasoft\DbSchema\Command\Seeder;
use Shasoft\DbSchema\Command\Title;
use Shasoft\DbSchema\Command\DbSchemaType;
use Shasoft\DbSchema\Command\Variable;
use Shasoft\DbSchema\Command\MaxLength;
use Shasoft\DbSchema\Command\MinLength;
use Shasoft\DbSchema\Command\DefaultValue;
use Shasoft\DbSchema\Command\ConversionInput;
use Shasoft\DbSchema\Command\ConversionOutput;
use Shasoft\DbSchema\Exceptions\DbSchemaExceptionInvalidDefaultValueForType;

// Логическое значение
class ColumnBoolean extends Column
{
    // Конструктор
    public function __construct()
    {
        // Вызвать конструктор родителя
        parent::__construct();
        // Поддерживаемые команды
        // Установить команды
        $this->setCommand(new Title('Логическое значение'));
        $this->setCommand(new DefaultValue);
        $this->setCommand(new DbSchemaType('String'), false);
        $this->setCommand(new MinLength(1), false);
        $this->setCommand(new MaxLength(1), false);
        $this->setCommand(new Variable(false), false);
        $this->setCommand(new ConversionInput(self::class . '::input'), false);
        $this->setCommand(new ConversionOutput(self::class . '::output'), false);
        // Установить команду заполнитель
        $this->setCommand(new Seeder(self::class . '::value'));
    }
    // Событие добавления (удалено)
    protected function _onCommandDefaultValue(DefaultValue $command): void
    {
        $value = $command->value();
        if ($value !== null && $value !== true && $value !== false) {
            throw new DbSchemaExceptionInvalidDefaultValueForType(var_export($value, true), self::class);
        }
    }
    // PHP=>БД
    public static function input(?bool $value): ?string
    {
        return $value ? '+' : '-';
    }
    // БД=>PHP
    public static function output(?string $value): ?bool
    {
        return $value == '+' ? true : false;
    }
    // Получить случайное значение
    static public function value(): bool
    {
        return rand(0, 1) == 1 ? true : false;
    }
}
