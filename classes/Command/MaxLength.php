<?php

namespace Shasoft\DbSchema\Command;

// Максимальный размер
class MaxLength extends Base
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
