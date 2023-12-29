<?php

namespace Shasoft\DbSchema\Exceptions;

use Shasoft\DbSchema\DbSchemaException;


// Исключение
class DbSchemaExceptionIndexIsMissingInTheDatabaseTable extends DbSchemaException
{
    // Конструктор
    public function __construct(string $name, string $tabname)
    {
        parent::__construct("Индекс {$name} в таблице БД {$tabname} отсутствует");
    }
}
