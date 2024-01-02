<?php

namespace Shasoft\DbSchema\State;

use Shasoft\DbSchema\Command\Id;
use Shasoft\DbSchema\Command\Drop;
use Shasoft\DbSchema\DbSchemaState;
use Shasoft\DbSchema\Command\Comment;
use Shasoft\DbSchema\Command\TabName;
use Shasoft\DbSchema\Command\Classname;
use Shasoft\DbSchema\State\StateColumn;
use Shasoft\DbSchema\State\StateCommands;
use Shasoft\DbSchema\State\StateDatabase;
use Shasoft\DbSchema\Exceptions\DbSchemaExceptionIndexIsMissingInTheDatabaseTable;
use Shasoft\DbSchema\Exceptions\DbSchemaExceptionColumnIsMissingInTheDatabaseTable;

// Состояние таблицы базы данных
class StateTable extends StateCommands
{
    // Колонки
    protected array $columns = [];
    // Индексы
    protected array $indexes = [];
    // Список удаленных объектов
    protected array $drops = [];
    // Конструктор
    public function __construct(protected StateDatabase $stateDatabase, DbSchemaState $state, protected array $relations)
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
        return $this->value(Comment::class);
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
    // Сгенерировать заданное количество строк
    public function seeder(int $count = 1, int $procNULL = 10): array
    {
        $ret = [];
        // Определим поля с автоматической генерацией
        $autoIncrement = [];
        foreach ($this->columns() as $name => $column) {
            if ($column->hasAutoIncrement()) {
                $autoIncrement[$name] = 0;
            }
        }
        // Сгенерировать все строки
        while (count($ret) < $count) {
            // Сгенерировать строку
            $row = [];
            foreach ($this->columns() as $name => $column) {
                if ($column->hasAutoIncrement()) {
                    $autoIncrement[$name]++;
                    $row[$name] = $autoIncrement[$name];
                } else {
                    // Сгенерировать значение
                    $row[$name] = $column->seeder($procNULL);
                }
            }
            //
            $ret[] = $row;
        }
        return $ret;
    }
};
