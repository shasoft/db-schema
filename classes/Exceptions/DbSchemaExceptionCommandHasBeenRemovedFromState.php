<?php

namespace Shasoft\DbSchema\Exceptions;

use Shasoft\DbSchema\DbSchemaException;


// Исключение
class DbSchemaExceptionCommandHasBeenRemovedFromState extends DbSchemaException
{
    // Конструктор
    public function __construct(string $commandName, string  $stateName)
    {
        parent::__construct("Команда {$commandName} была удалена из состояния {$stateName}");
    }
}
