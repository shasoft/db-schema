<?php

namespace Shasoft\DbSchema\Command;

// Генератор данных
class Seeder extends Call
{
    // Получить значение
    public function value(): string
    {
        return $this->value;
    }
    // Сгенерировать значение
    public function generate(): mixed
    {
        return $this->call();
    }
};
