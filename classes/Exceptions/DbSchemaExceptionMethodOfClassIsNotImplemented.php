<?php

namespace Shasoft\DbSchema\Exceptions;

use Shasoft\DbSchema\DbSchemaException;


// Исключение
class DbSchemaExceptionMethodOfClassIsNotImplemented extends DbSchemaException
{
    // Конструктор
    public function __construct(string $classname, string  $method)
    {
        parent::__construct("Метод {$method} класса {$classname} не реализован");
    }
}
