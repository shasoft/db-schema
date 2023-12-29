<?php

namespace Shasoft\DbSchema\Command;

// Комментарий
class Comment extends Base
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
