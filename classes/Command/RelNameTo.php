<?php

namespace Shasoft\DbSchema\Command;

// Имя отношения в конечной таблице
class RelNameTo extends Base
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
