<?php

namespace Shasoft\DbSchema\Exceptions;

use Shasoft\DbSchema\DbSchemaException;


// Исключение
class DbSchemaExceptionInvalidDefaultValueForType extends DbSchemaException
{
    // Конструктор
    public function __construct(string $value, string  $typeName)
    {
        parent::__construct("Неверное значение {$value} по умолчанию для типа {$typeName}");
    }
}
