<?php

namespace Shasoft\DbSchema\Column;

use Shasoft\DbSchema\Command\Scale;
use Shasoft\DbSchema\Command\Seeder;
use Shasoft\DbSchema\Command\Comment;
use Shasoft\DbSchema\Command\DbSchemaType;
use Shasoft\DbSchema\Command\MaxValue;
use Shasoft\DbSchema\Command\MinValue;
use Shasoft\DbSchema\Column\ColumnReal;
use Shasoft\DbSchema\Command\Precision;
use Shasoft\DbSchema\State\StateCommands;
use Shasoft\DbSchema\Command\DefaultValue;
use Shasoft\DbSchema\Command\ConversionInput;
use Shasoft\DbSchema\Command\ConversionOutput;

// Число с фиксированной точностью
class ColumnDecimal extends Column
{
    // Конструктор
    public function __construct()
    {
        // Вызвать конструктор родителя
        parent::__construct();
        // Установить команды
        $this->setCommand(new DbSchemaType('Decimal', false));
        $this->setCommand(new Comment('Число с фиксированной точностью'));
        $this->setCommand(new DefaultValue());
        $this->setCommand(new Precision(6));
        $this->setCommand(new Scale(4));
        $this->setCommand(new ConversionInput(self::class . '::convert'), false);
        $this->setCommand(new ConversionOutput(self::class . '::convert'), false);
        $this->setCommand(new Seeder(self::class . '::value'));
    }
    // PHP=>БД / БД=>PHP
    public static function convert($value)
    {
        return floatval($value);
    }
    // Получить случайное значение
    public static function value(StateCommands $column): float
    {
        return self::random(
            $column->value(Precision::class),
            $column->value(Scale::class)
        );
    }
    // Получить случайное значение
    public static function random(int $precision, int $scale): float
    {
        $max = 10 ** $precision - 1;
        $ret = ColumnInteger::random(0, $max);
        if ($scale > 0 && $scale <= $precision) {
            $ret /= (10 ** $scale);
        }
        return $ret;
    }
}
