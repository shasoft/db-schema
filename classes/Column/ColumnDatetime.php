<?php

namespace Shasoft\DbSchema\Column;

use Shasoft\DbSchema\Column\Column;
use Shasoft\DbSchema\Command\Comment;
use Shasoft\DbSchema\Command\DbSchemaType;
use Shasoft\DbSchema\State\StateCommands;
use Shasoft\DbSchema\Command\DefaultValue;
use Shasoft\DbSchema\Command\ConversionInput;
use Shasoft\DbSchema\Command\ConversionOutput;
use Shasoft\DbSchema\Command\Seeder;

// Дата/время
class ColumnDatetime extends Column
{
    // Конструктор
    public function __construct()
    {
        // Вызвать конструктор родителя
        parent::__construct();
        // Установить команды
        $this->setCommand(new DbSchemaType('Datetime'), false);
        $this->setCommand(new Comment('Дата/время'));
        $this->setCommand(new DefaultValue());
        $this->setCommand(new ConversionInput(self::class . '::input'), false);
        $this->setCommand(new ConversionOutput(self::class . '::output'), false);
        $this->setCommand(new Seeder(self::class . '::value'), false);
    }
    // PHP=>БД
    public static function input(?\DateTime  $value): ?string
    {
        if (is_null($value)) {
            return null;
        }
        $value->setTimezone(new \DateTimeZone("UTC"));
        return $value->format('Y-m-d H:i:s');
    }
    // БД=>PHP
    public static function output(?string $value): ?\DateTime
    {
        if (is_null($value)) {
            return null;
        }
        // Время в UTC 
        $ret =  \DateTime::createFromFormat('Y-m-d H:i:s', $value, new \DateTimeZone('UTC'));
        //$ret->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        return $ret;
    }
    // Получить случайное значение
    public static function value(StateCommands $column): \DateTime
    {
        // - 10 лет от текущей даты
        return self::random(-365 * 24 * 60 * 60 * 10);
    }
    // Текущее время
    public static function now(): \DateTime
    {
        $ret =  new \DateTime('now', new \DateTimeZone('UTC'));
        // Убрать миллисекунды
        $ret->setTimestamp($ret->getTimestamp());
        //
        return $ret;
    }
    // Получить случайное значение
    public static function random(int|\DateTime $min, \DateTime |null $max = null): \DateTime
    {
        // Текущая дата
        if (is_null($max)) {
            $max = self::now();
        }
        // Может задано число?
        if (is_numeric($min)) {
            $dt = self::now();
            $dt->setTimestamp($max->getTimestamp() + $min);
            $min = $dt;
        }
        // Определить разницу в секундах
        $diff = $max->getTimestamp() - $min->getTimestamp();
        // Определить смещение от минимального значения
        $delta = ColumnInteger::random(0, $diff);
        // Сформировать результат
        $ret = $min;
        $ret->setTimestamp($ret->getTimestamp() + $delta);
        // Вернуть результат
        return $ret;
    }
};
