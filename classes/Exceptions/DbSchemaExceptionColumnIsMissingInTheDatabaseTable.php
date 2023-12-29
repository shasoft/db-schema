<?php

namespace Shasoft\DbSchema\Exceptions;

use Shasoft\DbSchema\DbSchemaException;


// Исключение
class DbSchemaExceptionColumnIsMissingInTheDatabaseTable extends DbSchemaException
{
    // Конструктор
    public function __construct(string $name, string $tabname)
    {
        parent::__construct("Колонка {$name} в таблице БД {$tabname} отсутствует");
    }
}
