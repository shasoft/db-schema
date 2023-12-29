<?php

namespace Shasoft\DbSchema\Column;

use Shasoft\DbSchema\Column\Column;
use Shasoft\DbSchema\Command\Seeder;
use Shasoft\DbSchema\Command\Comment;
use Shasoft\DbSchema\Command\DbSchemaType;
use Shasoft\DbSchema\Command\PdoParam;
use Shasoft\DbSchema\Command\Variable;
use Shasoft\DbSchema\Command\MaxLength;
use Shasoft\DbSchema\Command\MinLength;
use Shasoft\DbSchema\State\StateCommands;
use Shasoft\DbSchema\Command\DefaultValue;
use Shasoft\DbSchema\Command\ConversionOutput;

// Двоичные данные
class ColumnBinary extends Column
{
    // Конструктор
    public function __construct()
    {
        // Вызвать конструктор родителя
        parent::__construct();
        // Установить команды
        $this->setCommand(new Comment('Двоичные данные'));
        $this->setCommand(new DbSchemaType('Binary'), false);
        $this->setCommand(new PdoParam(\PDO::PARAM_LOB), false);
        $this->setCommand(new DefaultValue());
        $this->setCommand(new Variable(true));
        $this->setCommand(new MinLength(0));
        $this->setCommand(new MaxLength(255));
        $this->setCommand(new ConversionOutput(self::class . '::output'), false);
        $this->setCommand(new Seeder(self::class . '::value'));
    }
    // БД=>PHP
    public static function output(mixed $value): mixed
    {
        if (is_resource($value)) {
            $value = stream_get_contents($value);
        }
        return $value;
    }
    // Получить случайное значение
    public static function value(StateCommands $column): string
    {
        return self::random($column->value(MaxLength::class));
    }
    // Получить случайное значение
    public static function random(int $maxLength = 255): string
    {
        return random_bytes(
            random_int(
                min(intval($maxLength / 8), $maxLength),
                $maxLength
            )
        );
    }
}
