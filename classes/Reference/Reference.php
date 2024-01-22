<?php
// Справка по SQL
// https://habr.com/ru/articles/564390/

namespace Shasoft\DbSchema\Reference;

use Shasoft\DbSchema\Command\Name;
use Shasoft\DbSchema\Command\Create;
use Shasoft\DbSchema\DbSchemaCommands;
use Shasoft\DbSchema\Command\ReferenceTo;
use Shasoft\DbSchema\Command\DefaultValue;

// Ссылка 
class Reference extends DbSchemaCommands
{
    // Конструктор
    public function __construct()
    {
        // Вызвать конструктор родителя
        parent::__construct();
        // Обязательные команды
        $this->addRequiredCommand([
            ReferenceTo::class
        ]);
        // Поддерживаемые команды
        $this->addSupportCommand([
            Name::class,
            //DefaultValue::class
        ]);
    }
}
