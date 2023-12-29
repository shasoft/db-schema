<?php

namespace Shasoft\DbSchema\Driver;

use Shasoft\DbSchema\Command\Name;
use Shasoft\DbSchema\Command\Scale;
use Shasoft\DbSchema\DbSchemaDriver;
use Shasoft\DbSchema\Index\IndexKey;
use Shasoft\DbSchema\Command\Columns;
use Shasoft\DbSchema\Command\Comment;
use Shasoft\DbSchema\Command\DbSchemaType;
use Shasoft\DbSchema\Command\MaxValue;
use Shasoft\DbSchema\Command\MinValue;
use Shasoft\DbSchema\Command\Unsigned;
use Shasoft\DbSchema\Command\Variable;
use Shasoft\DbSchema\State\StateIndex;
use Shasoft\DbSchema\State\StateTable;
use Shasoft\DbSchema\Command\MaxLength;
use Shasoft\DbSchema\Command\Precision;
use Shasoft\DbSchema\Index\IndexUnique;
use Shasoft\DbSchema\State\StateColumn;
use Shasoft\DbSchema\Index\IndexPrimary;
use Shasoft\DbSchema\Command\DefaultValue;
use Shasoft\DbSchema\Command\AutoIncrement;
use Shasoft\DbSchema\DbSchemaCommandsChanges;
use Shasoft\Pdo\Connection\PdoConnectionMySql;
use Shasoft\DbSchema\Exceptions\DbSchemaExceptionNotImplemented;
use Shasoft\DbSchema\Exceptions\DbSchemaExceptionSqlDataTypeNotSupported;

// Драйвер миграций MySql
class DbSchemaDriverMySql extends DbSchemaDriver
{
    // Конструктор
    public function __construct()
    {
        // Вызвать конструктор родителя
        parent::__construct(PdoConnectionMySql::class);
        //
    }
    // Получить тип для колонки
    // https://metanit.com/sql/postgresql/2.3.php
    // https://www.postgresql.org/docs/current/datatype-numeric.html
    // https://habr.com/ru/articles/43336/
    protected function getType(StateColumn $stateColumn): string
    {
        $ret = '';
        // Тип SQL
        $sqlType = $stateColumn->value(DbSchemaType::class);
        switch ($sqlType) {
            case 'Integer': {
                    $maxValue = max(
                        abs($stateColumn->value(MinValue::class, 0)),
                        abs($stateColumn->value(MaxValue::class, 0))
                    );
                    // Просто целое число
                    if ($maxValue <= 127) {
                        $ret = 'TINYINT';
                    } else if ($maxValue <= 32767) {
                        $ret = 'SMALLINT';
                    } else if ($maxValue <= 8388607) {
                        $ret = 'MEDIUMINT';
                    } else if ($maxValue <= 2147483647) {
                        $ret = 'INT';
                    } else {
                        $ret = 'BIGINT';
                    }
                    if ($stateColumn->hasAutoIncrement()) {
                        $ret .= ' UNSIGNED';
                    }
                }
                break;
            case 'Real': {
                    $maxValue = max(
                        abs($stateColumn->value(MinValue::class, 0)),
                        abs($stateColumn->value(MaxValue::class, 0))
                    );
                    if ($maxValue <= 3.4028E+38) {
                        $ret = 'FLOAT';
                    } else {
                        $ret = 'DOUBLE';
                    }
                }
                break;
            case 'Decimal': {
                    $precision = $stateColumn->value(Precision::class);
                    $scale = $stateColumn->value(Scale::class);
                    $ret = 'DECIMAL(' . $precision . ',' . $scale . ')';
                }
                break;
            case 'String': {
                    $maxLength = $stateColumn->value(MaxLength::class, 32);
                    if ($maxLength < 8000) {
                        if ($stateColumn->value(Variable::class, false)) {
                            // Переменная длинна
                            $ret = 'VARCHAR(' . $maxLength . ')';
                        } else {
                            // Фиксированная длинна
                            $ret = 'CHAR(' . $maxLength . ')';
                        }
                    } else {
                        // Текст
                        if ($maxLength <= 255) {
                            $ret = 'TINYTEXT';
                        } else if ($maxLength <= 65535) {
                            $ret = 'TEXT';
                        } else if ($maxLength <= 16777215) {
                            $ret = 'MEDIUMTEXT';
                        } else {
                            $ret = 'LONGTEXT';
                        }
                    }
                }
                break;
            case 'Datetime': {
                    $ret = 'DATETIME';
                }
                break;
            case 'Binary': {
                    $maxLength = $stateColumn->value(MaxLength::class, 32);
                    // Двоичные данные
                    if ($maxLength <= 255) {
                        $ret = 'TINYBLOB';
                    } else if ($maxLength <= 65535) {
                        $ret = 'BLOB';
                    } else if ($maxLength <= 16777215) {
                        $ret = 'MEDIUMBLOB';
                    } else {
                        $ret = 'LONGBLOB';
                    }
                }
                break;
            default: {
                    throw new DbSchemaExceptionSqlDataTypeNotSupported($stateColumn);
                }
                break;
        }
        return $ret;
    }
    // Получить команду для поля
    protected function sqlForColumn(StateColumn $stateColumn): string
    {
        // Имя поля
        $ret = '';
        // Тип
        $ret .= $this->getType($stateColumn);
        // Беззнаковое
        if ($stateColumn->value(Unsigned::class, false)) {
            $ret .= ' UNSIGNED';
        }
        // NULL
        $stateColumn->valueHas($ret, DefaultValue::class, function (string &$ret, mixed $value) {
            $ret .= ' ' . (is_null($value) ? 'NULL' : ('DEFAULT ' . json_encode($value)));
        });
        // AUTO_INCREMENT
        $stateColumn->valueHas($ret, AutoIncrement::class, function (string &$ret, bool $value) {
            $ret .= ($value ? ' AUTO_INCREMENT' : '');
        });
        // Комментарий
        $stateColumn->valueHas($ret, Comment::class, function (string &$ret, string $value) {
            $ret .= ' COMMENT ' . "'" . addslashes($value) . "'";
        });
        //
        return $ret;
    }
    // Типы
    static private array $typesMap = [
        IndexPrimary::class => 'PRIMARY KEY',
        IndexUnique::class => 'UNIQUE INDEX',
        IndexKey::class => 'INDEX'
    ];
    // Получить команду для индекса
    protected function sqlForIndex(StateIndex $index): string
    {
        // Список полей
        $fields = array_map(function ($fieldname) {
            $tmp = explode('(', $fieldname);
            $ret = $this->pdoConnection->quote($tmp[0]);
            if (count($tmp) > 1) {
                $ret .= '(' . intval(trim($tmp[1])) . ')';
            }
            return $ret;
        }, $index->value(Columns::class));
        // Тип
        $ret = self::$typesMap[$index->type()];
        // Имя
        if ($index->type() != IndexPrimary::class) {
            $ret .= ' ' . $this->pdoConnection->quote($index->name());
        }
        // Список полей
        //$ret .= ' (' . implode(',', $fields) . ')';
        $ret .= ' (' . implode(',', $index->get(Columns::class)->fields($this->pdoConnection)) . ')';

        // Алгоритм
        $ret .= ' USING BTREE';
        //
        return $ret;
    }
    /////// Методы ////////////
    /////// Таблица ////////////
    // Создание таблицы
    protected function onTableCreate(StateTable $state): array
    {
        $sql = '';
        //-- Установить таблицу
        $lines = [];
        foreach ($state->columns(0) as $key => $column) {
            $lines[] = $this->pdoConnection->quote($column->name()) . ' ' . $this->sqlForColumn($column);
        }
        foreach ($state->indexes() as $key => $index) {
            $lines[] = $this->sqlForIndex($index);
        }
        $sql = "CREATE TABLE " . $this->pdoConnection->quote($this->tabname($state->name())) . " (\n" . implode(", \n", $lines) . "\n )";
        // Комментарий
        $state->valueHas($sql, Comment::class, function (string &$sql, string $value) {
            $sql .= ' COMMENT ' . "'" . addslashes($value) . "'";
        });
        $sql .= ';';
        return [$sql];
    }
    // Удаление таблицы
    protected function onTableDrop(StateTable $state): array
    {
        return [
            $this->pdoConnection->sqlDropTable($this->tabname($state->name()))
        ];
    }
    // Изменение таблицы
    protected function onTableChange(StateTable $stateFrom, StateTable $stateTo, DbSchemaCommandsChanges $changeCommands): array
    {
        if ($changeCommands->count() == 1) {
            if ($changeCommands->has(Comment::class)) {
                return [
                    'ALTER TABLE ' .
                        $this->pdoConnection->quote($this->tabname($stateTo->name())) .
                        ' COMMENT ' . "'" . addslashes($stateTo->comment()) . "'"
                ];
            }
        } else {
            throw new DbSchemaExceptionNotImplemented;
        }
        return [];
    }
    /////// Колонка ////////////
    // Создание колонки
    protected function onColumnCreate(StateColumn $state): array
    {
        return [
            'ALTER TABLE ' .
                $this->pdoConnection->quote($this->tabname($state->table()->name())) .
                ' ADD ' .
                $this->pdoConnection->quote($state->name()) .
                ' ' .
                $this->sqlForColumn($state)
        ];
    }
    // Удаление колонки
    protected function onColumnDrop(StateColumn $state): array
    {
        return [
            'ALTER TABLE ' .
                $this->pdoConnection->quote($this->tabname($state->table()->name())) .
                ' DROP COLUMN ' .
                $this->pdoConnection->quote($state->name()) . ';'
        ];
    }
    // Изменение колонки
    protected function onColumnChange(StateColumn $stateFrom, StateColumn $stateTo, DbSchemaCommandsChanges $changeCommands): array
    {
        // Имя изменилось?
        //s_dump($changeCommands, $changeCommands->has(Name::class));
        if ($changeCommands->has(Name::class)) {
            return [
                'ALTER TABLE ' .
                    $this->pdoConnection->quote($this->tabname($stateTo->table()->name())) .
                    ' CHANGE ' .
                    $this->pdoConnection->quote($stateFrom->name()) .
                    ' ' .
                    $this->pdoConnection->quote($stateTo->name()) .
                    ' ' .
                    $this->sqlForColumn($stateTo) .
                    ';'
            ];
        } else {
            return [
                'ALTER TABLE ' .
                    $this->pdoConnection->quote($this->tabname($stateTo->table()->name())) .
                    ' MODIFY ' .
                    $this->pdoConnection->quote($stateTo->name()) .
                    //' ' .
                    $this->sqlForColumn($stateTo)
            ];
        }
        return [];
    }
    /////// Индекс ////////////
    // Создание индекса
    protected function onIndexCreate(StateIndex $state): array
    {
        // SQL код создания индекса
        //ALTER TABLE `shasoft-migration-tests-table-article`	ADD INDEX `author` (`userId`);
        $sql = 'ALTER TABLE ' . $this->pdoConnection->quote($this->tabname($state->table()->name())) . ' ADD ' . $this->sqlForIndex($state);
        return [$sql];
    }
    // Удаление индекса
    protected function onIndexDrop(StateIndex $state): array
    {
        // DROP INDEX `PRIMARY` ON t;
        // ALTER TABLE `shasoft-migration-tests-table-article` DROP INDEX `author`;
        $sql = 'ALTER TABLE ' . $this->pdoConnection->quote($this->tabname($state->table()->name())) . ' DROP INDEX ';
        if ($state->type() == IndexPrimary::class) {
            $sql .= $this->pdoConnection->quote('PRIMARY KEY');
        } else {
            $sql .= $this->pdoConnection->quote($state->name());
        }
        return [$sql];
    }
    // Изменение индекса
    protected function onIndexChange(StateIndex $stateFrom, StateIndex $stateTo, DbSchemaCommandsChanges $changeCommands): array
    {
        return array_merge(
            $this->onIndexDrop($stateFrom),
            $this->onIndexCreate($stateTo)
        );
    }
};
