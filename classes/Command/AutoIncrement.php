<?php

namespace Shasoft\DbSchema\Command;

// Автоинкрементное поле
class AutoIncrement extends Base
{
    // Конструктор
    public function __construct(protected bool $value = true)
    {
    }
    // Получить значение
    public function value(): bool
    {
        return $this->value;
    }
};
