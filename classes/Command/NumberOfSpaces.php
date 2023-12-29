<?php

namespace Shasoft\DbSchema\Command;

// Количество пробелов
class NumberOfSpaces extends Base
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
