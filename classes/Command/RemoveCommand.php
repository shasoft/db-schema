<?php

namespace Shasoft\DbSchema\Command;

// Удалить команду
class RemoveCommand extends Base
{
    // Конструктор
    public function __construct(protected ?string $name)
    {
    }
    // Получить значение
    public function value(): string
    {
        return $this->name;
    }
};
