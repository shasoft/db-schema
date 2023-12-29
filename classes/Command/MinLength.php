<?php

namespace Shasoft\DbSchema\Command;

// Минимальный размер
class MinLength extends Base
{
    // Конструктор
    public function __construct(protected int $value)
    {
    }
    // Получить значение
    public function value(): int
    {
        return $this->value;
    }
};
