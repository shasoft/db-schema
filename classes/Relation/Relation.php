<?php

namespace Shasoft\DbSchema\Relation;

use Shasoft\DbSchema\Command\Type;
use Shasoft\DbSchema\Command\RelName;
use Shasoft\DbSchema\Command\HasOne;
use Shasoft\DbSchema\Command\RelNameTo;
use Shasoft\DbSchema\Command\HasOneTo;
use Shasoft\DbSchema\DbSchemaCommands;
use Shasoft\DbSchema\Command\RelTableTo;
use Shasoft\DbSchema\Command\Columns;

// Отношение
abstract class Relation extends DbSchemaCommands
{
    // Конструктор
    public function __construct()
    {
        // Вызвать конструктор родителя
        parent::__construct();
        // Поддерживаемые команды
        $this->addSupportCommand([
            Type::class,
            RelNameTo::class
        ]);
        // Обязательные команды
        $this->addRequiredCommand([
            Columns::class,
            RelName::class,
            HasOne::class,
            HasOneTo::class,
            RelTableTo::class
        ]);
    }
}
