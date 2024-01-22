<?php

namespace Shasoft\DbSchema\Exceptions;

use Shasoft\DbSchema\DbSchemaException;


// Исключение
class DbSchemaExceptionIncorrectParameterTypeSpecifiedForSettingExtraData extends DbSchemaException
{
    // Конструктор
    public function __construct(?string $typename, string $name)
    {
        if (is_null($typename)) $typename = 'null';
        parent::__construct("Для установки дополнительных данных указан неправильный тип `{$typename}` параметра `{$name}`");
    }
}
