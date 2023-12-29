<?php

namespace Shasoft\DbSchema\Index;

use Shasoft\DbSchema\Command\Type;
use Shasoft\DbSchema\DbSchemaCommands;
use Shasoft\DbSchema\Command\Columns;

// Индекс таблицы
abstract class Index extends DbSchemaCommands
{
    // Конструктор
    public function __construct()
    {
        // Вызвать конструктор родителя
        parent::__construct();
        // Поддерживаемые команды
        $this->addSupportCommand([
            Type::class,
        ]);
        // Обязательные команды
        $this->addRequiredCommand([
            Columns::class
        ]);
    }
    // Индекс является уникальным?
    abstract public function hasUnique(): bool;
}
