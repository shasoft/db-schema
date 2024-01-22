<?php

namespace Shasoft\DbSchema\Exceptions;

use Shasoft\DbSchema\DbSchemaException;


// Исключение
class DbSchemaExceptionRelationIsMissingInTheDatabaseTable extends DbSchemaException
{
    // Конструктор
    public function __construct(string $name, string $tabname)
    {
        parent::__construct("Отношение {$name} в таблице БД {$tabname} отсутствует");
    }
}
