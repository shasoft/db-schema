<?php

namespace Shasoft\DbSchema\Driver;

use Shasoft\DbSchema\Command\Name;
use Shasoft\DbSchema\Command\Scale;
use Shasoft\DbSchema\Index\IndexKey;
use Shasoft\DbSchema\Command\Comment;
use Shasoft\DbSchema\Command\DbSchemaType;
use Shasoft\DbSchema\DbSchemaDriver;
use Shasoft\DbSchema\Command\MaxValue;
use Shasoft\DbSchema\Command\MinValue;
use Shasoft\DbSchema\Command\Variable;
use Shasoft\DbSchema\State\StateIndex;
use Shasoft\DbSchema\State\StateTable;
use Shasoft\DbSchema\Command\MaxLength;
use Shasoft\DbSchema\Command\Precision;
use Shasoft\DbSchema\Index\IndexUnique;
use Shasoft\DbSchema\State\StateColumn;
use Shasoft\DbSchema\Index\IndexPrimary;
use Shasoft\DbSchema\Command\Columns;
use Shasoft\DbSchema\Command\DefaultValue;
use Shasoft\DbSchema\DbSchemaCommandsChanges;
use Shasoft\DbSchema\Exceptions\DbSchemaExceptionNotImplemented;
use Shasoft\DbSchema\Exceptions\DbSchemaExceptionSqlDataTypeNotSupported;

// Драйвер миграций PostgreSql
class DbSchemaDriverPostgreSql extends DbSchemaDriver
{
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
                    if ($stateColumn->hasAutoIncrement()) {
                        // Автоинкрементирующееся целое число
                        if ($maxValue <= 32767) {
                            $ret = 'SMALLSERIAL';
                        } else if ($maxValue <= 2147483647) {
                            $ret = 'SERIAL';
                        } else {
                            $ret = 'BIGSERIAL';
                        }
                    } else {
                        // Просто целое число
                        if ($maxValue <= 32767) {
                            $ret = 'SMALLINT';
                        } else if ($maxValue <= 2147483647) {
                            $ret = 'INTEGER';
                        } else {
                            $ret = 'BIGINT';
                        }
                    }
                }
                break;
            case 'Real': {
                    $maxValue = max(
                        abs($stateColumn->value(MinValue::class, 0)),
                        abs($stateColumn->value(MaxValue::class, 0))
                    );
                    if ($maxValue <= 3.4028E+38) {
                        $ret = 'REAL';
                    } else {
                        $ret = 'DOUBLE PRECISION';
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
                            $ret = 'CHARACTER VARYING(' . $maxLength . ')';
                        } else {
                            // Фиксированная длинна
                            $ret = 'CHARACTER(' . $maxLength . ')';
                        }
                    } else {
                        $ret = 'TEXT';
                    }
                }
                break;
            case 'Datetime': {
                    $ret = 'TIMESTAMP';
                }
                break;
            case 'Binary': {
                    $ret = 'BYTEA';
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
        // Тип поля
        $ret = $this->getType($stateColumn);
        // Если это не автоинкрементирующиеся поле
        if (!$stateColumn->hasAutoIncrement()) {
            // Добавить атрибут
            $stateColumn->valueHas($ret, DefaultValue::class, function (string &$ret, mixed $value) use ($stateColumn) {
                $ret .= ' ' . (is_null($value) ? 'NULL' : ("NOT NULL DEFAULT '" . addslashes(($stateColumn->input($value)))) . "'");
            });
        }
        //
        return $ret;
    }
    /////// Методы ////////////
    // Поддерживаемые PDO драйверы
    public function pdoSupport(): array
    {
        return ['pgsql'];
    }
    // Заключить имя таблицы/колонки в кавычки
    public function quote(string $name): string
    {
        return '"' . $name . '"';
    }
    /////// Таблица ////////////
    // Создание таблицы
    protected function onTableCreate(StateTable $state): array
    {
        //
        $ret = [];
        //-- Установить таблицу
        $lines = [];
        foreach ($state->columns() as $key => $column) {
            $lines[] = $this->quote($column->name()) . ' ' . $this->sqlForColumn($column);
            // Добавить атрибут комментария
            $column->valueHas($ret, Comment::class, function (array &$ret, string $value) use ($column) {
                $ret[] =
                    "COMMENT ON COLUMN " .
                    $this->quote($this->tabname($column->table()->name())) .
                    "." .
                    $this->quote($column->name()) .
                    " IS '" . addslashes($value) . "'";
            });
        }
        foreach ($state->indexes() as $key => $index) {
            // Создание индекса
            $ret = array_merge($ret, $this->onIndexCreate($index));
        }
        // Команда создания таблицы
        $sqlCreateTable = "CREATE TABLE " . $this->quote($this->tabname($state->name())) . " (" . implode(", ", $lines) . ")";
        // Комментарий
        $state->valueHas($ret, Comment::class, function (array &$ret, string $value) use ($state) {
            $ret[] = "COMMENT ON TABLE " .
                $this->quote($this->tabname($state->name())) .
                " IS '" .
                addslashes($value) .
                "'";
        });
        //
        return array_merge([$sqlCreateTable], $ret);
    }
    // Изменение таблицы
    protected function onTableChange(StateTable $stateFrom, StateTable $stateTo, DbSchemaCommandsChanges $changeCommands): array
    {
        // COMMENT ON TABLE "contact" IS 'Изменение комментария для таблицы';
        if ($changeCommands->count() == 1) {
            if ($changeCommands->has(Comment::class)) {
                return [
                    "COMMENT ON TABLE " .
                        $this->quote($this->tabname($stateTo->name())) .
                        " IS '" .
                        addslashes($stateTo->comment()) .
                        "'"
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
                $this->quote($this->tabname($state->table()->name())) .
                ' ADD ' .
                $this->quote($state->name()) .
                ' ' .
                $this->sqlForColumn($state)
        ];
    }
    // Удаление колонки
    protected function onColumnDrop(StateColumn $state): array
    {
        return [
            'ALTER TABLE ' .
                $this->quote($this->tabname($state->table()->name())) .
                ' DROP COLUMN ' .
                $this->quote($state->name()) . ';'
        ];
    }
    // Изменение колонки
    protected function onColumnChange(StateColumn $stateFrom, StateColumn $stateTo, DbSchemaCommandsChanges $changeCommands): array
    {
        // COMMENT ON COLUMN "contact"."id" IS 'Изменение комментария для колонки';
        $ret = [];
        // https://metanit.com/sql/postgresql/2.6.php
        // Имя изменилось?
        if ($changeCommands->has(Name::class)) {
            // ALTER TABLE table_name RENAME COLUMN old_column_name TO new_column_name;
            $ret[] =
                'ALTER TABLE ' .
                $this->quote($this->tabname($stateTo->table()->name())) .
                ' RENAME COLUMN ' .
                $this->quote($stateFrom->name()) .
                ' TO ' .
                $this->quote($stateTo->name()) .
                ';';
        }
        // Если тип изменился
        $sqlType = $this->getType($stateTo);
        if ($sqlType != $this->getType($stateFrom)) {
            $ret[] =
                'ALTER TABLE ' .
                $this->quote($this->tabname($stateTo->table()->name())) .
                ' ALTER COLUMN ' .
                $this->quote($stateTo->name()) .
                ' TYPE ' .
                $sqlType .
                ';';
        }
        if ($changeCommands->has(DefaultValue::class)) {
            //
            $defaultValue = $changeCommands->get(DefaultValue::class)->value();
            //
            $sqlPrefix =
                'ALTER TABLE ' .
                $this->quote($this->tabname($stateTo->table()->name())) .
                ' ALTER COLUMN ' .
                $this->quote($stateTo->name());
            //
            if (is_null($defaultValue)) {
                $ret[] = $sqlPrefix . ' DROP NOT NULL';
                $ret[] = $sqlPrefix . ' SET DEFAULT NULL';
            } else {
                $ret[] = $sqlPrefix . " SET DEFAULT '" . addslashes(($stateTo->input($defaultValue))) . "'";
                $ret[] = $sqlPrefix . ' SET NOT NULL';
            }
        }
        // Если комментарий изменился
        if ($changeCommands->has(Comment::class)) {
            // Добавить команду изменения комментария
            $stateTo->valueHas($ret, Comment::class, function (array &$ret, string $value) use ($stateTo) {
                $ret[] =
                    "COMMENT ON COLUMN " .
                    $this->quote($this->tabname($stateTo->table()->name())) .
                    "." .
                    $this->quote($stateTo->name()) .
                    " IS '" . addslashes($value) . "'";
            });
        }
        return $ret;
    }
    /////// Индекс ////////////
    // Типы индексов
    static private array $typesMap = [
        IndexPrimary::class => 'UNIQUE INDEX',
        IndexUnique::class => 'UNIQUE INDEX',
        IndexKey::class => 'INDEX'
    ];
    // Имя индекса
    protected function indexName(StateIndex $state): string
    {
        return $state->name() . '_' . strtoupper(hash('crc32', $state->table()->name()));
    }
    // Создание индекса
    protected function onIndexCreate(StateIndex $state): array
    {
        // SQL код создания индекса
        return [
            'CREATE ' .
                self::$typesMap[$state->type()] . ' ' .
                $this->quote($this->indexName($state)) . ' ON ' .
                $this->quote($this->tabname($state->table()->name())) .
                ' (' . implode(',', $state->get(Columns::class)->fields($this)) . ')'
        ];
    }
    // Удаление индекса
    protected function onIndexDrop(StateIndex $state): array
    {
        // DROP INDEX `PRIMARY` ON t;
        // ALTER TABLE `shasoft-migration-tests-table-article` DROP INDEX `author`;
        $sql = 'DROP INDEX ' . $this->quote($this->indexName($state));
        return [$sql];
    }
};
