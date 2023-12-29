<?php

namespace Shasoft\DbSchema\State;

use Shasoft\DbSchema\Command\Name;
use Shasoft\DbSchema\Command\Type;
use Shasoft\DbSchema\State\StateTable;
use Shasoft\DbSchema\State\StateCommands;

// Дочерний элемент таблицы
class StateTableChild extends StateCommands
{
    // Конструктор
    public function __construct(protected StateTable $stateTable, array $commands)
    {
        parent::__construct($commands);
    }
    // Таблица
    public function table(): StateTable
    {
        return $this->stateTable;
    }
    // Имя
    public function name(): string
    {
        return $this->value(Name::class);
    }
    // Тип
    public function type(): string
    {
        return $this->value(Type::class);
    }
};
