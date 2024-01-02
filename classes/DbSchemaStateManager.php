<?php

namespace Shasoft\DbSchema;

use Shasoft\DbSchema\Command\Id;
use Shasoft\DbSchema\Index\Index;
use Shasoft\DbSchema\Table\Table;
use Shasoft\DbSchema\Command\Drop;
use Shasoft\DbSchema\Command\Name;
use Shasoft\DbSchema\Command\Type;
use Shasoft\DbSchema\DbSchemaReflection;
use Shasoft\DbSchema\Column\Column;
use Shasoft\DbSchema\Command\AutoIncrement;
use Shasoft\DbSchema\Command\Migration;
use Shasoft\DbSchema\Command\Create;
use Shasoft\DbSchema\Command\Comment;
use Shasoft\DbSchema\Command\RelName;
use Shasoft\DbSchema\Command\ICommand;
use Shasoft\DbSchema\Command\RelTable;
use Shasoft\DbSchema\State\StateTable;
use Shasoft\DbSchema\Command\Classname;
use Shasoft\DbSchema\Command\RelNameTo;
use Shasoft\DbSchema\Relation\Relation;
use Shasoft\DbSchema\Command\ReferenceTo;
use Shasoft\DbSchema\Reference\Reference;
use Shasoft\DbSchema\State\StateCommands;
use Shasoft\DbSchema\State\StateDatabase;
use Shasoft\DbSchema\Exceptions\DbSchemaExceptionCommandIsNotSupported;
use Shasoft\DbSchema\Exceptions\DbSchemaExceptionRequiredCommandNotDefined;

// Менеджер состояний
class DbSchemaStateManager
{
    // Список таблиц
    protected array $tables = [];
    // Конструктор
    protected function __construct(array $tableClasses)
    {
        foreach ($tableClasses as $tableClass) {
            $this->tables[] = $this->parseTable($tableClass);
        }
    }
    // Текущее состояние базы данных
    static public function get(array $tableClasses): StateDatabase
    {
        // Создать менеджер состояний
        $stateManager = new static($tableClasses);
        // Получить актуальную БД
        $ret = null;
        foreach ($stateManager->getDbSchemaMigrations() as $migrationCurrent) {
            //
            $tables = [];
            foreach ($stateManager->tables as $tableItem) {
                $table = $tableItem->writeCommands($migrationCurrent);
                if ($table) {
                    //
                    $names = ['columns', 'indexes', 'relations', 'references'];
                    foreach ($names as $name) {
                        $items = [];
                        foreach ($tableItem->getValue($name) as $iItem) {
                            $item = $iItem->writeCommands($migrationCurrent);
                            if ($item) {
                                $items[] = $item;
                            }
                        }
                        $table->setValue($name, $items);
                    }
                    // Добавить таблицу
                    $tables[$table->get(Classname::class)->value()] = $table;
                }
            }
            // Сгенерировать список всех колонок
            $allColumns = [];
            foreach ($tables as $tableClass => $table) {
                foreach ($table->getValue('columns') as $column) {
                    $allColumns[$tableClass . '.' . $column->value(Id::class)] = $column;
                }
                // Выполнить проверку обязательных команд
                foreach ($table->getValue('references') as $reference) {
                    DbSchemaExceptionRequiredCommandNotDefined::check($reference, $tableClass);
                }
            }
            // Заменить все ссылки на колонки-образец
            do {
                $countNewColumns = 0;
                foreach ($tables as $name => $table) {
                    foreach ($table->getValue('references') as $reference) {
                        // Текущая колонка
                        $keyColumn = $name . '.' . $reference->value(Id::class);
                        // Если текущая колонка ещё не добавлена
                        if (!array_key_exists($keyColumn, $allColumns)) {
                            // Ссылка на колонку
                            $keySample = $reference->value(ReferenceTo::class);
                            // Если есть колонка, на которую идет ссылка
                            if (array_key_exists($keySample, $allColumns)) {
                                // то клонировать колонку
                                $column = clone $allColumns[$keySample];
                                // Добавить комментарий по умолчанию из комментария исходной колонки
                                if ($column->has(Comment::class)) {
                                    $column->setCommand(new Comment('#' . $column->value(Comment::class)));
                                }
                                // Удалить команду AutoIncrement
                                if ($column->has(AutoIncrement::class)) {
                                    $column->removeCommand(AutoIncrement::class);
                                }
                                // Добавить в неё текущие команды
                                foreach ($reference->commands()  as $_command) {
                                    $column->setCommand($_command);
                                }
                                // Добавить колонку в общий список колонок
                                $allColumns[$keyColumn] = $column;
                                // Добавить колонки в список полей таблицы
                                $table->setValue(
                                    'columns',
                                    array_merge($table->getValue('columns'), [$column])
                                );
                                // Увеличить количество добавленных колонок
                                $countNewColumns++;
                            }
                        }
                    }
                }
            } while ($countNewColumns > 0);
            // Преобразовать список состояний в состояние базы данных
            $ret = new StateDatabase($migrationCurrent, $ret, $tables);
        }
        return $ret;
    }
    // Получить команды из массива состояний
    protected function getCommands(array $states): array
    {
        $ret = [];
        foreach ($states as $state) {
            $ret = array_merge($ret, $state->commands());
        }
        return $ret;
    }
    // Получить список миграций
    protected function getDbSchemaMigrations(): array
    {
        // Все команды
        $allCommands = $this->getCommands($this->tables);
        foreach ($this->tables as $table) {
            $allCommands = array_merge(
                $allCommands,
                $this->getCommands($table->getValue('columns')),
                $this->getCommands($table->getValue('indexes')),
                $this->getCommands($table->getValue('relations')),
                $this->getCommands($table->getValue('references')),
            );
        }
        // Определить список миграций
        $ret = [];
        foreach ($allCommands as $key => $command) {
            if (is_numeric($key)) {
                if ($command instanceof Migration) {
                    $ret[$command->value()] = 1;
                }
            }
        }
        $ret = array_keys($ret);
        sort($ret);
        //
        return $ret;
    }
    // Разобрать таблицу
    protected function parseTable(string $tableClass): DbSchemaState
    {
        $ret = new DbSchemaState;
        // Имя класса таблицы
        $ret->setValue('tableClass', $tableClass);
        // Рефлексия класса таблицы
        $refClass = new \ReflectionClass($tableClass);
        // А может первой идет команда Migration?
        $attributes = $refClass->getAttributes();
        // Установить время изменения
        $datetime = $ret->setDatetime($attributes, '0017-10-18T12:00:00+00:00');
        // Добавить идентификатор
        $ret->addCommand(new Id($tableClass));
        // Добавить имя класса таблицы
        $ret->addCommand(new Classname($tableClass));
        // По умолчанию описание таблицы - это её класс
        $ret->addCommand(new Comment($tableClass));
        // Команды из атрибутов
        $ret->addCommandFromAttributes($attributes, Table::class);
        // 
        $columns = [];
        $indexes = [];
        $relations = [];
        $references = [];
        foreach ($refClass->getProperties(\ReflectionProperty::IS_PROTECTED) as $property) {
            if (!$property->isStatic() && $property->hasType()) {
                // Тип поля
                $classnameType = (string)$property->getType();

                // Это колонка?
                if (is_a($classnameType, Column::class, true)) {
                    // Разобрать колонку
                    $columns[] = $this->parseColumn(
                        $tableClass,
                        $classnameType,
                        $property->getName(),
                        $property->getAttributes(),
                        $datetime
                    );
                }
                // Это индекс?
                else if (is_a($classnameType, Index::class, true)) {
                    // Разобрать индекс
                    $indexes[] = $this->parseIndex(
                        $tableClass,
                        $classnameType,
                        $property->getName(),
                        $property->getAttributes(),
                        $datetime
                    );
                }
                // Это отношение?
                else if (is_a($classnameType, Relation::class, true)) {
                    // Разобрать индекс
                    $relations[] = $this->parseRelation(
                        $tableClass,
                        $classnameType,
                        $property->getName(),
                        $property->getAttributes(),
                        $datetime
                    );
                }
                // Это отношение?
                else if (is_a($classnameType, Reference::class, true)) {
                    // Разобрать ссылку на другую колонку
                    $references[] = $this->parseReference(
                        $tableClass,
                        $classnameType,
                        $property->getName(),
                        $property->getAttributes(),
                        $datetime
                    );
                }
            }
        }
        //
        $ret->setValue('columns', $columns);
        $ret->setValue('indexes', $indexes);
        $ret->setValue('relations', $relations);
        $ret->setValue('references', $references);
        //
        return $ret;
    }
    // Разобрать колонку
    protected function parseColumn(string $tableClass, string $classnameType, string $name, array $attributes, string $datetime): DbSchemaState
    {
        // Создать
        $ret = new DbSchemaState;
        // Указать дата/время
        $ret->setValue('datetime', $datetime);
        // Имя класса таблицы
        $ret->setValue('tableClass', $tableClass);
        // Установить время изменения
        $datetime = $ret->setDatetime($attributes, $datetime);
        // Добавить идентификатор
        $ret->addCommand(new Id($name));
        // Наименование по умолчанию
        $ret->addCommand(new Name($name));
        // Наименование по умолчанию
        $ret->addCommand(new Type($classnameType));
        // Команды из атрибутов
        $ret->addCommandFromAttributes($attributes, $classnameType);
        // Вернуть
        return $ret;
    }
    // Разобрать индекс
    protected function parseIndex(string $tableClass, string $classnameType, string $name, array $attributes, string $datetime): DbSchemaState
    {
        // Создать
        $ret = new DbSchemaState;
        // Указать дата/время
        $ret->setValue('datetime', $datetime);
        // Имя класса таблицы
        $ret->setValue('tableClass', $tableClass);
        // Установить время изменения
        $datetime = $ret->setDatetime($attributes, $datetime);
        // Добавить время
        $ret->addCommand(new Migration($datetime));
        // Добавить идентификатор
        $ret->addCommand(new Id($name));
        // Наименование по умолчанию
        $ret->addCommand(new Type($classnameType));
        // Команды из атрибутов
        $ret->addCommandFromAttributes($attributes, $classnameType);
        // Вернуть
        return $ret;
    }
    // Разобрать отношение
    protected function parseRelation(string $tableClass, string $classnameType, string $name, array $attributes, string $datetime): DbSchemaState
    {
        // Создать
        $ret = new DbSchemaState;
        // Указать дата/время
        $ret->setValue('datetime', $datetime);
        // Имя класса таблицы
        $ret->setValue('tableClass', $tableClass);
        // Установить время изменения
        $datetime = $ret->setDatetime($attributes, $datetime);
        // Добавить время
        $ret->addCommand(new Migration($datetime));
        // Добавить идентификатор
        $ret->addCommand(new Id($name));
        // Добавить описание (по умолчанию имя)
        $ret->setCommand(new Comment($name));
        // Добавить имя отношения в текущей таблице
        $ret->setCommand(new RelName($name));
        // Добавить имя отношения в итоговой таблице
        $ret->setCommand(new RelNameTo($name . strtoupper(hash('crc32', $tableClass))));
        // Ссылочная таблица
        $ret->setCommand(new RelTable($tableClass));
        // Наименование по умолчанию
        $ret->addCommand(new Type($classnameType));
        // Команды из атрибутов
        $ret->addCommandFromAttributes($attributes, $classnameType);
        // Вернуть
        return $ret;
    }
    // Разобрать ссылку на другую колонку
    protected function parseReference(string $tableClass, string $classnameType, string $name, array $attributes, string $datetime): DbSchemaState
    {
        // Создать
        $ret = new DbSchemaState;
        // Указать дата/время
        $ret->setValue('datetime', $datetime);
        // Имя класса таблицы
        $ret->setValue('tableClass', $tableClass);
        // Установить время изменения
        $datetime = $ret->setDatetime($attributes, $datetime);
        // Добавить время
        $ret->addCommand(new Migration($datetime));
        //
        $ret->addCommand(new Create);
        // Добавить идентификатор
        $ret->addCommand(new Id($name));
        // Наименование по умолчанию
        $ret->addCommand(new Name($name));
        // Команды из атрибутов
        $ret->addCommandFromAttributes($attributes, $classnameType);
        // Вернуть
        return $ret;
    }
};
