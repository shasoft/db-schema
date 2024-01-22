<?php

namespace Shasoft\DbSchema\Column;

use Shasoft\DbSchema\Command\Title;
use Shasoft\DbSchema\Command\MaxValue;
use Shasoft\DbSchema\Command\MinValue;
use Shasoft\DbSchema\Command\DefaultValue;
use Shasoft\DbSchema\Column\ColumnInteger;
use Shasoft\DbSchema\Command\AutoIncrement;

// Идентификатор
class ColumnId extends ColumnInteger
{
    // Конструктор
    public function __construct()
    {
        // Вызвать конструктор родителя
        parent::__construct();
        // Установить команды
        $this->setCommand(new Title('Идентификатор'));
        $this->setCommand(new MinValue(0));
        $this->setCommand(new MaxValue(PHP_INT_MAX));
        //$this->setCommand(new DefaultValue(0), false);
        $this->setCommand(new AutoIncrement);
        // Удалить команды
        $this->removeCommand(DefaultValue::class);
        // Удалить поддерживаемую команду
        $this->removeSupportCommand(DefaultValue::class);
    }
};
