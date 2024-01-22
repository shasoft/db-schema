<?php

namespace Shasoft\DbSchema\Command;

// Имя первичного индекса
class Index~PrimaryName extends Base
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
