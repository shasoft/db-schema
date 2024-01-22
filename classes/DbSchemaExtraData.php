<?php

namespace Shasoft\DbSchema;

use Shasoft\DbSchema\DbSchemaReflection;
use Shasoft\DbSchema\State\StateCommands;

// Расширенные данные
class DbSchemaExtraData
{
    // Установить значение
    static public function set(StateCommands $stateCommands, string $key, mixed $value): void
    {
        // Получить ссылку на свойство
        $refExtraData = DbSchemaReflection::getObjectProperty($stateCommands, 'extraData');
        // Читать значение свойства
        $extraData = $refExtraData->getValue($stateCommands);
        // Установить новое значение
        $extraData[$key] = $value;
        // Записать свойство
        $refExtraData->setValue($stateCommands, $extraData);
    }
};
