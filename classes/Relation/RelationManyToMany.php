<?php

namespace Shasoft\DbSchema\Relation;

use Shasoft\DbSchema\Command\HasOne;
use Shasoft\DbSchema\Command\Title;
use Shasoft\DbSchema\Command\HasOneTo;

#[Title('Отношение многие-ко-многим')]
abstract class RelationManyToMany extends Relation
{
    // Конструктор
    public function __construct()
    {
        // Вызвать конструктор родителя
        parent::__construct();
        // Установить команды
        $this->setCommand(new HasOne(false));
        $this->setCommand(new HasOneTo(false));
    }
}
