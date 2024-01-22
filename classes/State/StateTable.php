<?php

namespace Shasoft\DbSchema\State;

use Shasoft\DbSchema\Command\Id;
use Shasoft\DbSchema\Command\Drop;
use Shasoft\DbSchema\Command\Title;
use Shasoft\DbSchema\DbSchemaState;
use Shasoft\DbSchema\Command\TabName;
use Shasoft\DbSchema\Command\Classname;
use Shasoft\DbSchema\State\StateColumn;
use Shasoft\DbSchema\State\StateCommands;
use Shasoft\DbSchema\State\StateDatabase;
use Shasoft\DbSchema\Exceptions\DbSchemaExceptionIndexIsMissingInTheDatabaseTable;
use Shasoft\DbSchema\Exceptions\DbSchemaExceptionColumnIsMissingInTheDatabaseTable;
use Shasoft\DbSchema\Exceptions\DbSchemaExceptionRelationIsMissingInTheDatabaseTable;

// Состояние таблицы базы данных
class StateTable extends StateCommands
{
    // Колонки
    protected array $columns = [];
    // Индексы
    protected array $indexes = [];
    // Отношения
    protected array $relations = [];
    // Список удаленных объектов
    protected array $drops = [];
    // Конструктор
    public function __construct(protected StateDatabase $stateDatabase, DbSchemaState $state, array $relations)
    {
        // Вызвать конструктор родителя
        parent::__construct($state->commands());
        // Колонки
        foreach ($state->getValue('columns') as $column) {
            $column = new StateColumn($this, $column->commands());
            // Если колонка удалена?
            if ($column->has(Drop::class)) {
                // то сохранить объект в список удаленных
                $this->drops[$column->get(Id::class)->value()] = $column;
            } else {
                // иначе сохранить в список колонок
                $this->columns[$column->name()] = $column;
            }
        }
        // Индексы
        foreach ($state->getValue('indexes') as $index) {
            $index = new StateIndex($this, $index->commands());
            // Если индекс удален?
            if ($index->has(Drop::class)) {
                // то сохранить объект в список удаленных
                $this->drops[$index->get(Id::class)->value()] = $index;
            } else {
                // иначе сохранить в список индексов
                $this->indexes[$index->name()] = $index;
            }
        }
        // Отношения
        foreach ($relations as $relation) {
            $this->relations[$relation->from()->name()] = $relation;
        }
    }
    // База данных
    public function database(): StateDatabase
    {
        return $this->stateDatabase;
    }
    // Имя класса таблицы
    public function name(): string
    {
        return $this->value(Classname::class);
    }
    // Имя таблицы
    public function tabname(): string
    {
        return $this->value(TabName::class);
    }
    // Комментарий
    public function comment(): string
    {
        return $this->value(Title::class);
    }
    // Список колонок
    public function columns(): array
    {
        return $this->columns;
    }
    // Проверить наличие колонки
    public function hasColumn(string $name): bool
    {
        return array_key_exists($name, $this->columns);
    }
    // Получить колонку
    public function column(string $name): StateColumn
    {
        if (array_key_exists($name, $this->columns)) {
            return $this->columns[$name];
        }
        throw new DbSchemaExceptionColumnIsMissingInTheDatabaseTable($name, $this->name());
    }
    // Список индексов
    public function indexes(): array
    {
        return $this->indexes;
    }
    // Проверить наличие индекса
    public function hasIndex(string $name): bool
    {
        return array_key_exists($name, $this->indexes);
    }
    // Получить индекс
    public function index(string $name): StateIndex
    {
        if (array_key_exists($name, $this->indexes)) {
            return $this->indexes[$name];
        }
        throw new DbSchemaExceptionIndexIsMissingInTheDatabaseTable($name, $this->name());
    }
    // Список отношений таблицы
    public function relations(): array
    {
        return $this->relations;
    }
    // Список отношений таблицы
    public function relation(string $name): StateRelation
    {
        if (array_key_exists($name, $this->indexes)) {
            return $this->relations[$name];
        }
        throw new DbSchemaExceptionRelationIsMissingInTheDatabaseTable($name, $this->name());
    }
};
