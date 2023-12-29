<?php

namespace Shasoft\DbSchema\Command;

// Миграция
class Migration extends Base
{
    // Значение
    protected string $value;
    // Конструктор
    public function __construct(string|\DateTime $value)
    {
        if (is_string($value)) {
            $value = new \DateTime($value);
        }
        $this->value = $value->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s.v+00:00');
    }
    // Дата
    public function value(): string
    {
        return $this->value;
    }
};
