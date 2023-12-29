<?php

namespace Shasoft\DbSchema\Exceptions;

use Shasoft\DbSchema\DbSchemaException;


// Исключение
class DbSchemaExceptionCommandIsNotSupported extends DbSchemaException
{
    // Конструктор
    public function __construct(string $commandClass, string  $classname)
    {
        parent::__construct("Команда {$commandClass} не поддерживается в классе {$classname}");
    }
}
