<?php

namespace Shasoft\DbSchema\Command;

// Точность
class Precision extends Base
{
    // Конструктор
    public function __construct(protected int $value = 2)
    {
    }
    // Получить значение
    public function value(): int
    {
        return $this->value;
    }
};
