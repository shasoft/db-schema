<?php

namespace Shasoft\DbSchema\Command;

// Минимальное значение
class MinValue extends Base
{
    // Конструктор
    public function __construct(protected int|float $value)
    {
    }
    // Получить значение
    public function value(): int|float
    {
        return $this->value;
    }
};
