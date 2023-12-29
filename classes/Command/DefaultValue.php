<?php

namespace Shasoft\DbSchema\Command;

// Значение по умолчанию
class DefaultValue extends Base
{
    // Конструктор
    public function __construct(protected mixed $value = null)
    {
    }
    // Получить значение
    public function value(): mixed
    {
        return $this->value;
    }
};
