<?php

namespace Shasoft\DbSchema\Column;

use Shasoft\DbSchema\Column\Column;
use Shasoft\DbSchema\Command\Seeder;
use Shasoft\DbSchema\Command\Title;
use Shasoft\DbSchema\Command\DbSchemaType;
use Shasoft\DbSchema\Command\Variable;
use Shasoft\DbSchema\Command\MaxLength;
use Shasoft\DbSchema\Command\MinLength;
use Shasoft\DbSchema\State\StateCommands;
use Shasoft\DbSchema\Command\DefaultValue;
use Shasoft\DbSchema\Command\NumberOfSpaces;
use Shasoft\DbSchema\Command\ConversionOutput;

// Текст
class ColumnString extends Column
{
    // Конструктор
    public function __construct()
    {
        // Вызвать конструктор родителя
        parent::__construct();
        // Установить команды
        $this->setCommand(new DbSchemaType('String'), false);
        $this->setCommand(new Title('Текст'));
        $this->setCommand(new DefaultValue());
        $this->setCommand(new Variable(true));
        $this->setCommand(new MinLength(1));
        $this->setCommand(new MaxLength(255));
        $this->setCommand(new NumberOfSpaces(0));
        $this->setCommand(new Seeder(self::class . '::value'));
        $this->setCommand(new ConversionOutput(self::class . '::output'), false);
    }
    // PHP=>БД / БД=>PHP
    public static function output(mixed $value, StateCommands $column): mixed
    {
        if (!$column->value(Variable::class, false)) {
            return trim($value);
        }
        return $value;
    }
    // Получить случайное значение
    public static function value(StateCommands $column): string
    {
        return self::random(
            $column->value(MaxLength::class),
            $column->value(MinLength::class),
            $column->value(NumberOfSpaces::class)
        );
    }
    // Получить случайное значение
    public static function random(int $maxLength, int $minLength, int $spaces): string
    {
        $ret = '';
        //
        if ($maxLength == 0) $maxLength = $minLength;
        // Длинна строки
        $length = ColumnInteger::random($minLength, $maxLength);
        if ($length > 0) {
            $ret = '';
            while ($spaces > 0) {
                $size = intval($length / ($spaces + 1));
                $minV = min(2, $size);
                $maxV = max(10, $size);
                $len = ColumnInteger::random($minV, $maxV);
                if ($len > 0) {
                    $ret .= self::randomString($len);
                    $length -= $len;
                    $ret .= ' ';
                    $length--;
                }
                $spaces--;
            }
        }
        $ret = trim($ret);
        // Определим остаток
        $len = strlen($ret);
        $length = ColumnInteger::random($minLength, $maxLength);
        if ($len < $length) {
            $ret .= self::randomString($length - $len);
        }
        //s_dd($minLength, $maxLength, $length, $len, strlen($ret));
        //
        return $ret;
    }
    // Сгенерировать случайную строку заданной длинны
    static public function randomString(int $length): string
    {
        $ret = '';
        for ($i = 0; $i < $length; $i++) {
            $ret .= chr(random_int(ord('A'), ord('Z')));
        }
        return $ret;
    }
};
