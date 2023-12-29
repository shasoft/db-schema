<?php

namespace Shasoft\DbSchema\Command;

// Перечисление
class Enum extends Base
{
    // Есть тип?
    protected bool $hasType;
    // Список значений
    protected array $items;
    // Список значений для конвертации
    protected array $outputValues;
    // Конструктор
    public function __construct(protected mixed $value)
    {
        // Установить значения по классу
        $this->init();
    }
    // Установить значения по классу
    private function init(): void
    {
        // Есть тип?
        $this->hasType = (new \ReflectionClass($this->value()))->hasMethod('from');
        // Получить значения
        $this->items = call_user_func($this->value() . '::cases');
        // Список значений для конвертации
        $this->outputValues = [];
        foreach ($this->items as $item) {
            $key = $this->hasType ? $item->value : $item->name;
            $this->outputValues[$key] = $item;
        }
    }
    // Получить значение
    public function value(): mixed
    {
        return $this->value;
    }
    // Получить значения
    public function items(): array
    {
        return $this->items;
    }
    // Получить значения
    public function hasType(): bool
    {
        return $this->hasType;
    }
    // Конвертировать PHP=>БД
    public function input(mixed $value): mixed
    {
        if (is_null($value)) {
            return null;
        }
        return $this->hasType ? $value->value : $value->name;
    }
    // Конвертировать БД=>PHP
    public function output(mixed $value): mixed
    {
        if (is_null($value)) {
            return null;
        }
        if (is_string($value)) $value = trim($value);
        return $this->outputValues[$value] ?? null;
    }
    public function __serialize(): array
    {
        return [$this->value];
    }
    public function __unserialize(array $data): void
    {
        $this->value = $data[0];
        // Установить значения по классу
        $this->init();
    }
};
