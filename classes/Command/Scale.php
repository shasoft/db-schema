<?php

namespace Shasoft\DbSchema\Command;

// Максимальное количество цифр, которые может содержать число после запятой
class Scale extends Base
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
