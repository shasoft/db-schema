<?php

namespace Shasoft\DbSchema;

use Shasoft\Pdo\PdoConnection;
use Shasoft\DbSchema\Command\Id;
use Shasoft\DbSchema\State\StateIndex;
use Shasoft\DbSchema\State\StateTable;
use Shasoft\DbSchema\State\StateColumn;
use Shasoft\DbSchema\State\StateDatabase;
use Shasoft\DbSchema\DbSchemaCommandsChanges;
use Shasoft\DbSchema\Exceptions\DbSchemaExceptionCommandHasBeenRemovedFromState;

// Драйвер миграций
abstract class DbSchemaDriver
{
    // PDO соединение 
    protected PdoConnection $pdoConnection;
    // Конструктор
    public function __construct(string $pdoConnectionClass)
    {
        $this->pdoConnection = new $pdoConnectionClass([]);
    }
    // Сгенерировать набор команд для перевода состояние $from в состояние $to
    public function diff(?StateDatabase $from, ?StateDatabase $to): array
    {
        return $this->diffInt($from, $to, null);
    }
    // Сгенерировать набор команд для перевода состояние $from в состояние $to с проверкой только по таблице $tabname
    private function diffInt(?StateDatabase $from, ?StateDatabase $to, ?string $tabname): array
    {
        // Если не указано начальное состояние
        if (is_null($from)) {
            $from = new StateDatabase('null', null, []);
        }
        // Если не указано конечное состояние
        if (is_null($to)) {
            $to = new StateDatabase('null', null, []);
        }
        // Если не указана конкретная таблица
        if (is_null($tabname)) {
            // то сравнивать всё
            $tablesFrom = $from->tables();
            $tablesTo = $to->tables();
        } else {
            $tablesFrom = array_filter($from->tables(), function (string $key) use ($tabname) {
                return ($tabname == $key);
            }, ARRAY_FILTER_USE_KEY);
            $tablesTo = array_filter($to->tables(), function (string $key) use ($tabname) {
                return ($tabname == $key);
            }, ARRAY_FILTER_USE_KEY);
        }
        // Сравнить таблицы
        $ret = $this->processArray(
            $tablesFrom,
            $tablesTo,
            'diffTable'
        );
        //s_dump($ret);
        return $ret;
    }
    // Преобразовать массив объектов контейнеров команд в массив с идентификаторами в качестве ключей
    private function processArrayToIds(array $items): array
    {
        $ret = [];
        foreach ($items as $toKey => $commandContainer) {
            $ret[$commandContainer->value(Id::class)] = $commandContainer;
        }
        return $ret;
    }
    // Определить какие элементы в массиве to по сравнению с from были
    // 1. Create = Добавлены
    // 2. Change - Изменены
    // 3. Drop - Удалены
    private function processArray(array $from, array $to, string $methodName): array
    {
        $ret = [];
        // Преобразовать в массив с идентификаторами в качестве ключей
        $from = $this->processArrayToIds($from);
        $to = $this->processArrayToIds($to);
        // Удаление и изменение
        foreach ($from as $fromKey => $fromValue) {
            // Если в конечном состоянии элемента' нет
            $hasExists = !array_key_exists($fromKey, $to);
            // Если нет
            if ($hasExists) {
                // то это событие удаления
                $ret = array_merge($ret, $this->$methodName('Drop', $fromValue, null, null));
            } else {
                // иначе это ВОЗМОЖНО событие изменения
                $toValue = $to[$fromKey];
                //*
                $ret = array_merge(
                    $ret,
                    $this->$methodName(
                        'Change',
                        $fromValue,
                        $toValue,
                        new DbSchemaCommandsChanges($fromValue, $toValue)
                    )
                );
                //*/
                /*
                //
                $valueFrom = serialize($fromValue);
                $valueTo = serialize($toValue);
                // Если есть изменения
                if ($valueFrom != $valueTo) {
                    //
                    $ret = array_merge(
                        $ret,
                        $this->$methodName(
                            'Change',
                            $fromValue,
                            $toValue,
                            new DbSchemaCommandsChanges($fromValue, $toValue)
                        )
                    );
                }
                //*/
            }
        }
        // Создание
        foreach ($to as $toKey => $toValue) {
            // Если в начальном состоянии элемента нет
            if (!array_key_exists($toKey, $from)) {
                // то это событие создания
                $ret = array_merge($ret, $this->$methodName('Create', null, $toValue, null));
            }
        }
        return $ret;
    }
    // Сравнение таблицы
    private function diffTable(string $cmd, ?StateTable $from, ?StateTable $to, ?DbSchemaCommandsChanges $changeCommands): array
    {
        $ret = [];
        switch ($cmd) {
            case 'Create': {
                    $ret = $this->onTableCreate($to);
                }
                break;
            case 'Drop': {
                    $ret = $this->onTableDrop($from);
                }
                break;
            case 'Change': {
                    // Если есть изменения
                    if (!$changeCommands->empty()) {
                        // то вызвать событие
                        $ret = $this->onTableChange($from, $to, $changeCommands);
                    }
                    // Сравнить колонки
                    $ret = array_merge($ret, $this->processArray($from->columns(), $to->columns(), 'diffColumn'));
                    // Сравнить индексы
                    $ret = array_merge($ret, $this->processArray($from->indexes(), $to->indexes(), 'diffIndex'));
                }
                break;
        }
        return $ret;
    }
    // Сравнение колонки
    private function diffColumn(string $cmd, ?StateColumn $from, ?StateColumn $to, ?DbSchemaCommandsChanges $changeCommands): array
    {
        $ret = [];
        switch ($cmd) {
            case 'Create': {
                    $ret = $this->onColumnCreate($to);
                }
                break;
            case 'Drop': {
                    $ret = $this->onColumnDrop($from);
                }
                break;
            case 'Change': {
                    // Если есть изменения
                    if (!$changeCommands->empty()) {
                        // то вызвать событие
                        $ret = $this->onColumnChange($from, $to, $changeCommands);
                    }
                }
                break;
        }
        return $ret;
    }
    // Сравнение индексы
    private function diffIndex(string $cmd, ?StateIndex $from, ?StateIndex $to, ?DbSchemaCommandsChanges $changeCommands): array
    {
        $ret = [];
        switch ($cmd) {
            case 'Create': {
                    $ret = $this->onIndexCreate($to);
                }
                break;
            case 'Drop': {
                    $ret = $this->onIndexDrop($from);
                }
                break;
            case 'Change': {
                    // Если есть изменения
                    if (!$changeCommands->empty()) {
                        // то вызвать событие
                        $ret = $this->onIndexChange($from, $to, $changeCommands);
                    }
                }
                break;
        }
        return $ret;
    }
    // Имя класса Pdo соединения
    public function pdoConnectionClass(): string
    {
        return get_class($this->pdoConnection);
    }
    /////// Методы ////////////
    // Имя таблицы
    public function tabname(string $classname): string
    {
        return strtolower(str_replace('\\', '-', $classname));
    }
    /////// Таблица ////////////
    // Создание таблицы
    abstract protected function onTableCreate(StateTable $state): array;
    // Удаление таблицы
    abstract protected function onTableDrop(StateTable $state): array;
    // Изменение таблицы
    abstract protected function onTableChange(StateTable $stateFrom, StateTable $stateTo, DbSchemaCommandsChanges $changeCommands): array;
    /////// Колонка ////////////
    // Создание колонки
    abstract protected function onColumnCreate(StateColumn $state): array;
    // Удаление колонки
    abstract protected function onColumnDrop(StateColumn $state): array;
    // Изменение колонки
    abstract protected function onColumnChange(StateColumn $stateFrom, StateColumn $stateTo, DbSchemaCommandsChanges $changeCommands): array;
    /////// Индекс ////////////
    // Создание индекса
    abstract protected function onIndexCreate(StateIndex $state): array;
    // Удаление индекса
    abstract protected function onIndexDrop(StateIndex $state): array;
    // Изменение индекса
    abstract protected function onIndexChange(StateIndex $stateFrom, StateIndex $stateTo, DbSchemaCommandsChanges $changeCommands): array;
};
