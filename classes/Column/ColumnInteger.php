<?php

namespace Shasoft\DbSchema\Column;

use Shasoft\DbSchema\Column\Column;
use Shasoft\DbSchema\Command\Seeder;
use Shasoft\DbSchema\Command\Title;
use Shasoft\DbSchema\Command\MaxValue;
use Shasoft\DbSchema\Command\MinValue;
use Shasoft\DbSchema\Command\PdoParam;
use Shasoft\DbSchema\State\StateCommands;
use Shasoft\DbSchema\Command\DbSchemaType;
use Shasoft\DbSchema\Command\DefaultValue;
use Shasoft\DbSchema\Command\AutoIncrement;
use Shasoft\DbSchema\Command\ConversionInput;
use Shasoft\DbSchema\Command\ConversionOutput;

// Целое число
class ColumnInteger extends Column
{
    // Конструктор
    public function __construct()
    {
        // Вызвать конструктор родителя
        parent::__construct();
        // Установить команды
        $this->setCommand(new DbSchemaType('Integer'), false);
        $this->setCommand(new PdoParam(\PDO::PARAM_INT), false);
        $this->setCommand(new Title('Целое число'));
        $this->setCommand(new MinValue(PHP_INT_MIN));
        $this->setCommand(new MaxValue(PHP_INT_MAX));
        $this->setCommand(new DefaultValue());
        $this->setCommand(new ConversionInput(self::class . '::convert'), false);
        $this->setCommand(new ConversionOutput(self::class . '::convert'), false);
        $this->setCommand(new Seeder(self::class . '::value'));
        //
        $this->addSupportCommand(AutoIncrement::class);
    }
    // PHP=>БД / БД=>PHP
    public static function convert(mixed $value): mixed
    {
        return intval($value);
    }
    // Получить случайное значение
    public static function value(StateCommands $column): int
    {
        return self::random($column->value(MinValue::class), $column->value(MaxValue::class));
    }
    // Получить случайное значение
    public static function random(int|float $min, int|float $max): int
    {
        //
        return intval($min + lcg_value() * abs($max - $min));
    }
};
