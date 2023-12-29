<?php

namespace Shasoft\DbSchema\Command;

// Тип данных схемы
class DbSchemaType extends Base
{
    // Конструктор
    public function __construct(protected string $value)
    {
    }
    // Получить значение
    public function value(): string
    {
        return $this->value;
    }
};
