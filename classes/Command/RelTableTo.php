<?php

namespace Shasoft\DbSchema\Command;

// Имя конечной таблицы
class RelTableTo extends Base
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
