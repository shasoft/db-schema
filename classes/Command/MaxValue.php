<?php

namespace Shasoft\DbSchema\Command;

// Максимальное значение
class MaxValue extends Base
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
