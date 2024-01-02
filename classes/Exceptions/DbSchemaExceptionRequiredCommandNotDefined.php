<?php

namespace Shasoft\DbSchema\Exceptions;

use Shasoft\DbSchema\DbSchemaReflection;
use Shasoft\DbSchema\DbSchemaState;
use Shasoft\DbSchema\DbSchemaException;


// Исключение
class DbSchemaExceptionRequiredCommandNotDefined extends DbSchemaException
{
    // Конструктор
    public function __construct(string $name, string $classname, string  $tableClass)
    {
        parent::__construct("Не определена обязательная команда {$name} для класса {$classname} в классе таблицы {$tableClass}");
    }
    // Выполнить проверку обязательных команд
    static public function check(DbSchemaState $state, string $tableClass): void
    {
        if ($state->has(Type::class)) {
            $classname = $state->get(Type::class)->value();
            foreach (array_keys(DbSchemaReflection::getObjectPropertyValue(new $classname, 'requiredCommands', [])) as $requiredCommand) {
                if (!$state->has($requiredCommand)) {
                    throw new static($requiredCommand, $classname, $tableClass);
                }
            }
        }
    }
}
