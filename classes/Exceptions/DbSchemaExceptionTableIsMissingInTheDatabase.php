<?php

namespace Shasoft\DbSchema\Exceptions;

use Shasoft\DbSchema\DbSchemaException;


// Исключение
class DbSchemaExceptionTableIsMissingInTheDatabase extends DbSchemaException
{
    // Конструктор
    public function __construct(string $tabname)
    {
        parent::__construct("Таблица {$tabname} в БД отсутствует");
    }
}
