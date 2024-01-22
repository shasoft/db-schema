<?php

namespace Shasoft\DbSchema\Column;

use Shasoft\DbSchema\Column\Column;
use Shasoft\DbSchema\Command\Seeder;
use Shasoft\DbSchema\Command\Title;
use Shasoft\DbSchema\Command\DbSchemaType;
use Shasoft\DbSchema\Command\MaxValue;
use Shasoft\DbSchema\Command\MinValue;
use Shasoft\DbSchema\Command\Scale;
use Shasoft\DbSchema\State\StateCommands;
use Shasoft\DbSchema\Command\DefaultValue;
use Shasoft\DbSchema\Command\ConversionInput;
use Shasoft\DbSchema\Command\ConversionOutput;

// Вещественное число
class ColumnReal extends Column
{
    // Конструктор
    public function __construct()
    {
        // Вызвать конструктор родителя
        parent::__construct();
        // Установить команды
        $this->setCommand(new DbSchemaType('Real'), false);
        $this->setCommand(new Title('Вещественное число'));
        $this->setCommand(new DefaultValue());
        $this->setCommand(new MinValue(PHP_FLOAT_MIN));
        $this->setCommand(new MaxValue(PHP_FLOAT_MAX));
        $this->setCommand(new Scale(2));
        $this->setCommand(new ConversionInput(self::class . '::convert'), false);
        $this->setCommand(new ConversionOutput(self::class . '::convert'), false);
        $this->setCommand(new Seeder(self::class . '::value'));
    }
    // PHP=>БД / БД=>PHP
    public static function convert(mixed $value): mixed
    {
        return floatval($value);
    }
    // Получить случайное значение
    public static function value(StateCommands $column): float
    {
        return self::random(
            $column->value(MinValue::class),
            $column->value(MaxValue::class),
            $column->value(Scale::class)
        );
    }
    // Получить случайное значение
    public static function random(float $min, float $max, int $scale): float
    {
        if (is_infinite($min)) {
            $max = PHP_FLOAT_MIN;
        }
        if (is_infinite($max)) {
            $max = PHP_FLOAT_MAX;
        }
        $val = ($min + lcg_value() * (abs($max - $min)));
        return round($val, $scale);
    }
    // Разбить вещественное число
    public static function split(float $value): array
    {
        $ret = [
            'value' => $value
        ];
        $str = (string)$value;
        $pos = strpos($str, 'E');
        $posZpt = strpos($str, '.');
        if ($pos === false) {
            $ret['n'] = 0;
        } else {
            $ret['n'] = intval(substr($str, $pos + 1));
        }
        $ret['v'] = floatval(substr($str, 0, $pos));
        $ret['scale'] = $pos - $posZpt - 1;
        //
        return $ret;
    }
    // Сравнить два числа
    public static function compare(?float $a, ?float $b): bool
    {
        if (is_null($a) != is_null($b)) {
            return false;
        }
        if (is_null($a) == is_null($b)) {
            return true;
        }
        $a = self::split($a);
        $b = self::split($b);
        $scale = min($a['scale'], $b['scale']);
        if ($a['n'] > 0 && $scale > 6) {
            if ($a['n'] > 16) {
                $scale = 5;
            } else {
                $scale = 6;
            }
        }
        return $a['n'] == $b['n'] && round($a['v'], $scale) == round($b['v'], $scale);
    }
}
