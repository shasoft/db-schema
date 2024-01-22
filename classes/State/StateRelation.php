<?php

namespace Shasoft\DbSchema\State;

use Shasoft\DbSchema\Command\Title;
use Shasoft\DbSchema\DbSchemaReflection;
use Shasoft\DbSchema\Command\RelName;
use Shasoft\DbSchema\Command\HasOne;
use Shasoft\DbSchema\Command\RelTable;
use Shasoft\DbSchema\Command\HasOneTo;
use Shasoft\DbSchema\Command\Columns;
use Shasoft\DbSchema\State\StateCommands;
use Shasoft\DbSchema\DbSchemaRelationDirection;

// Состояние отношения
class StateRelation extends StateCommands
{
    // Конструктор
    public function __construct(protected StateDatabase $parent, protected DbSchemaRelationDirection $from, protected DbSchemaRelationDirection $to, array $commands)
    {
        parent::__construct($commands);
    }
    // Откуда
    public function from(): DbSchemaRelationDirection
    {
        return $this->from;
    }
    // Куда
    public function to(): DbSchemaRelationDirection
    {
        return $this->to;
    }
    // Описание
    public function comment(): string
    {
        return $this->value(Title::class);
    }
    // Связь между ОТКУДА=>КУДА
    public function on(): array
    {
        return $this->value(Columns::class);
    }
};
