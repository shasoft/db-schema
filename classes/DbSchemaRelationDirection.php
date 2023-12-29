<?php

namespace Shasoft\DbSchema;

use Shasoft\DbSchema\State\StateTable;
use Shasoft\DbSchema\State\StateDatabase;

// Направление для отношения
class DbSchemaRelationDirection
{
    // Конструктор
    public function __construct(
        protected StateDatabase $stateDatabase,
        protected bool $hasDefined,
        protected string $tabname,
        protected string $name,
        protected array $columns,
        protected bool $one
    ) {
    }
    // Отношение определено в этой таблице
    public function hasDefined(): bool
    {
        return $this->hasDefined;
    }
    // Имя таблицы
    public function tabname(): string
    {
        return $this->tabname;
    }
    // Таблица
    public function table(): StateTable
    {
        return $this->stateDatabase->table($this->tabname);
    }
    // Имя
    public function name(): string
    {
        return $this->name;
    }
    // Колонки
    public function columns(): array
    {
        return $this->columns;
    }
    // Это связь ОДИН?
    public function one(): bool
    {
        return $this->one;
    }
    // Это связь МНОГО?
    public function many(): bool
    {
        return !$this->one;
    }
};
