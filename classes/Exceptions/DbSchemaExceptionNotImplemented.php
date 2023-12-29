<?php

namespace Shasoft\DbSchema\Exceptions;

use Shasoft\DbSchema\DbSchemaException;


// Исключение
class DbSchemaExceptionNotImplemented extends DbSchemaException
{
    // Конструктор
    public function __construct()
    {
        parent::__construct("Не реализовано");
    }
}
