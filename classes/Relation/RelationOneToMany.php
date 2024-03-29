<?php

namespace Shasoft\DbSchema\Relation;

use Shasoft\DbSchema\Command\HasOne;
use Shasoft\DbSchema\Command\Title;
use Shasoft\DbSchema\Command\HasOneTo;

#[Title('Отношение один-ко-многим')]
class RelationOneToMany extends Relation
{
    // Конструктор
    public function __construct()
    {
        // Вызвать конструктор родителя
        parent::__construct();
        // Установить команды
        $this->setCommand(new HasOne(true));
        $this->setCommand(new HasOneTo(false));
    }
}
