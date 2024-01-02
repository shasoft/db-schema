<?php

namespace Shasoft\DbSchema\State;

use Shasoft\DbSchema\Command\Id;
use Shasoft\DbSchema\Command\Drop;
use Shasoft\DbSchema\Command\Type;
use Shasoft\DbSchema\DbSchemaReflection;
use Shasoft\DbSchema\DbSchemaState;
use Shasoft\DbSchema\Command\HasOne;
use Shasoft\DbSchema\Command\Origin;
use Shasoft\DbSchema\DbSchemaDriver;
use Shasoft\DbSchema\Index\IndexKey;
use Shasoft\DbSchema\Command\Columns;
use Shasoft\DbSchema\Command\RelName;
use Shasoft\DbSchema\Command\HasOneTo;
use Shasoft\DbSchema\Command\RelTable;
use Shasoft\DbSchema\State\StateTable;
use Shasoft\DbSchema\Command\RelNameTo;
use Shasoft\DbSchema\Index\IndexUnique;
use Shasoft\DbSchema\Command\RelTableTo;
use Shasoft\DbSchema\DbSchemaMigrations;
use Shasoft\DbSchema\State\StateCommands;
use Shasoft\DbSchema\State\StateRelation;
use Shasoft\DbSchema\DbSchemaRelationDirection;
use Shasoft\DbSchema\Exceptions\DbSchemaExceptionTableIsMissingInTheDatabase;

// Состояние базы данных
class StateDatabase extends StateCommands
{
    // Поддерживаемые PDO соединения
    protected array $pdoSupport;
    // Список таблиц
    protected array $tables = [];
    // Список удаленных объектов
    protected array $drops = [];
    // Конструктор
    public function __construct(protected string $migrationName, protected ?StateDatabase $parent, array $tables)
    {
        // Список отношений
        $relations = [];
        foreach ($tables as $table) {
            foreach ($table->getValue('relations') as $relation) {
                //
                $stateCommands = new StateCommands($relation->commands());
                //
                $columns = $stateCommands->value(Columns::class);
                //
                $from = new DbSchemaRelationDirection(
                    $this,
                    true,
                    $stateCommands->value(RelTable::class),
                    $stateCommands->value(RelName::class),
                    $columns,
                    $stateCommands->value(HasOne::class)
                );
                $to = new DbSchemaRelationDirection(
                    $this,
                    false,
                    $stateCommands->value(RelTableTo::class),
                    $stateCommands->value(RelNameTo::class),
                    array_flip($columns),
                    $stateCommands->value(HasOneTo::class)
                );
                //
                $relations[] = new StateRelation(
                    $this,
                    $from,
                    $to,
                    array_merge($relation->commands(), [Origin::class => new Origin])
                );
                $relations[] = new StateRelation(
                    $this,
                    $to,
                    $from,
                    $relation->commands()
                );
            }
        }
        // Разобрать отношения по таблицам с указаниям источника
        $relationForTable = [];
        foreach ($tables as $classname => $tableState) {
            $relationForTable[$classname] = [];
        }
        foreach ($relations as $relation) {
            // Имя таблицы
            $tabname = $relation->from()->tabname();
            // Установить отношение
            $relationForTable[$tabname][] = $relation;
            // Список колонок индекса
            $columns = array_keys($relation->from()->columns());
            // Строка полей
            $columnsIndexStr = $this->columnsToString($columns);
            // Список всех индексов
            $indexes = $tables[$tabname]->getValue('indexes');
            // Проверить: а может нужный индекс по этим полям уже есть?
            $findIndexes = array_filter(
                $indexes,
                function (DbSchemaState $index) use ($columnsIndexStr) {
                    $_columnsIndexStr = $this->columnsToString($index->get(Columns::class)->value());
                    return ($columnsIndexStr == $_columnsIndexStr);
                }
            );
            if (empty($findIndexes)) {
                // Добавить индекс для отношения
                $indexes[] = new DbSchemaState(
                    [
                        Id::class => new Id($relation->from()->name()),
                        Type::class => new Type($relation->from()->one() ? IndexUnique::class : IndexKey::class),
                        Columns::class => new Columns($columns),
                    ]
                );
                // Установить новый список индексов
                $tables[$tabname]->setValue('indexes', $indexes);
            }
        }
        // Создать все таблицы
        foreach ($tables as $classname => $tableState) {
            $table = new StateTable($this, $tableState, $relationForTable[$classname]);
            // Если колонка не удалена?
            if ($table->has(Drop::class)) {
                $this->drops[$table->name()] = $table;
            } else {
                $this->tables[$table->name()] = $table;
            }
        }
    }
    // Получить строку полей
    protected function columnsToString(array $columns): string
    {
        return implode(',', $columns);
    }
    // Имя миграции
    public function name(): string
    {
        return $this->migrationName;
    }
    // Имя предыдущего состояния
    public function parent(): ?static
    {
        return $this->parent;
    }
    // Самая первое состояние
    public function first(): static
    {
        $ret = $this;
        $parent = $this->parent();
        while (!is_null($parent)) {
            $ret = $parent;
            $parent = $parent->parent();
        }
        return $ret;
    }
    // Драйвер PDO поддерживается?
    public function hasPdoSupport(string $name): bool
    {
        return in_array($name, $this->pdoSupport, true);
    }
    // Список таблиц
    public function tables(): array
    {
        return $this->tables;
    }
    // Проверить наличие таблицы
    public function hasTable(string $tableClass): bool
    {
        return array_key_exists($tableClass, $this->tables);
    }
    // Получить таблицу
    public function table(string $tableClass): StateTable
    {
        if (array_key_exists($tableClass, $this->tables)) {
            return $this->tables[$tableClass];
        }
        throw new DbSchemaExceptionTableIsMissingInTheDatabase($tableClass);
    }
    // Получить миграции
    public function migrations(DbSchemaDriver $driver): array
    {
        $ret = [];
        //
        $stateDatabases = [];
        $tabNames = [];
        $state = $this;
        while ($state) {
            $stateDatabases[] = $state;
            $tabNames = array_merge($tabNames, array_keys($state->tables()));
            $state = $state->parent();
        }
        $tabNames = array_unique($tabNames);
        $stateDatabases[] = null;
        $stateDatabases = array_reverse($stateDatabases);
        $refMethodDiffInt = DbSchemaReflection::getObjectMethod($driver, 'diffInt');
        foreach ($tabNames as $tabname) {
            for ($i = 1; $i < count($stateDatabases); $i++) {
                $up = $refMethodDiffInt->invoke($driver, $stateDatabases[$i - 1], $stateDatabases[$i], $tabname);
                $down = $refMethodDiffInt->invoke($driver, $stateDatabases[$i], $stateDatabases[$i - 1], $tabname);
                //
                if (!empty($up) || !empty($down)) {
                    $name = $stateDatabases[$i]->name();
                    //
                    if (!array_key_exists($name, $ret)) {
                        // Клонировать
                        $state = unserialize(serialize($stateDatabases[$i]));
                        // Обнулить родительскую БД
                        $state->parent = null;
                        // Удалить из таблиц все удаленные объекты
                        foreach ($state->tables() as $table) {
                            DbSchemaReflection::getObjectProperty($table, 'drops')->setValue($table, []);
                        }
                        //
                        $ret[$name] = [
                            'state' => $state,
                            'migrations' => []
                        ];
                    }
                    if (!array_key_exists($tabname, $ret[$name]['migrations'])) {
                        $ret[$name]['migrations'][$tabname] = [
                            'up' => [],
                            'down' => []
                        ];
                    }
                    $ret[$name]['migrations'][$tabname]['up'] = array_merge($ret[$name]['migrations'][$tabname]['up'], $up);
                    $ret[$name]['migrations'][$tabname]['down'] = array_merge($ret[$name]['migrations'][$tabname]['down'], $down);
                }
            }
        }
        $ret = array_values($ret);
        // Сортировать по миграциям
        usort($ret, function (array $item1, array $item2) {
            return strcmp($item1['state']->name(), $item2['state']->name());
        });
        //
        return $ret;
    }
    // Очистки БД (удаление ДАННЫХ всех таблиц[кроме таблицы миграций])
    public function clear(\PDO $pdo): void
    {
        /*
        foreach ($pdoConnection->tables() as $tabname) {
            if ($tabname != DbSchemaMigrations::getTechTable()) {
                $pdoConnection->sql($pdoConnection->sqlClearTable($tabname))->exec();
            }
        }
        //*/
    }
};
