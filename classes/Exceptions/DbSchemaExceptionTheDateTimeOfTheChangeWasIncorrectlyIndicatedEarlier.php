<?php

namespace Shasoft\DbSchema\Exceptions;

use Shasoft\DbSchema\DbSchemaException;


// Исключение
class DbSchemaExceptionTheDateTimeOfTheChangeWasIncorrectlyIndicatedEarlier extends DbSchemaException
{
    // Конструктор
    public function __construct(string $classname, string $datetime, string  $datetimeNew)
    {
        parent::__construct("В классе {$classname} дата/время изменения {$datetimeNew} ошибочно указано ранее {$datetime}");
    }
}
