<?php

namespace Shasoft\DbSchema\Command;

// Тип конечной стороны отношения
class HasOneTo extends Base
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
