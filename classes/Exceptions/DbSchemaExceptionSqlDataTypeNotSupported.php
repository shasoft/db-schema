<?php

namespace Shasoft\DbSchema\Exceptions;

use Shasoft\DbSchema\Command\Id;
use Shasoft\DbSchema\Command\DbSchemaType;
use Shasoft\DbSchema\State\StateColumn;
use Shasoft\DbSchema\DbSchemaException;

// Исключение
class DbSchemaExceptionSqlDataTypeNotSupported extends DbSchemaException
{
    // Конструктор
    public function __construct(StateColumn $column)
    {
        parent::__construct(
            "SQL тип данных " .
                $column->value(DbSchemaType::class) .
                " не поддерживается (класс таблицы '" .
                $column->table()->name() .
                "', колонка '" . $column->value(Id::class) . "')"
        );
    }
}
