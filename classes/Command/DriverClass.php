<?php

namespace Shasoft\DbSchema\Command;

// Имя класса драйвера
class DriverClass extends Base
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
