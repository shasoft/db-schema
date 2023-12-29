<?php

namespace Shasoft\DbSchema\Command;

// Переменное значение 
class Variable extends Base
{
    // Конструктор
    public function __construct(protected bool $value)
    {
    }
    // Получить значение
    public function value(): bool
    {
        return $this->value;
    }
};
