<?php
// Справка по SQL
// https://habr.com/ru/articles/564390/

namespace Shasoft\DbSchema\Column;

use Shasoft\DbSchema\Command\Name;
use Shasoft\DbSchema\Command\Type;
use Shasoft\DbSchema\Command\Seeder;
use Shasoft\DbSchema\Command\PdoParam;
use Shasoft\DbSchema\DbSchemaCommands;
use Shasoft\DbSchema\Command\ConversionInput;
use Shasoft\DbSchema\Command\ConversionOutput;
use Shasoft\DbSchema\Command\Custom;

// Поле таблицы
abstract class Column extends DbSchemaCommands
{
    // Конструктор
    public function __construct()
    {
        // Вызвать конструктор родителя
        parent::__construct();
        // Установить команды по умолчанию
        $this->setCommand(new PdoParam(\PDO::PARAM_STR), false);
        // Поддерживаемые команды
        $this->addSupportCommand([
            Custom::class,
            Type::class,
            ConversionInput::class,
            ConversionOutput::class,
            Name::class,
            Seeder::class
        ]);
    }
}
