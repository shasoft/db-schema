<?php

namespace Shasoft\DbSchema\Exceptions;

use Shasoft\DbSchema\DbSchemaException;


// Исключение
class DbSchemaExceptionTheIndexContainsANonExistentField extends DbSchemaException
{
    // Конструктор
    public function __construct(string $tabname, string $name, string  $fieldname)
    {
        parent::__construct("В индексе {$name} таблицы {$tabname} указано несуществующее поле {$fieldname}");
    }
}
