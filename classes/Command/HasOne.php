<?php

namespace Shasoft\DbSchema\Command;

// Тип начальной стороны отношения
class HasOne extends Base
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
