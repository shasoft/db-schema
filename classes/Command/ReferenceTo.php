<?php

namespace Shasoft\DbSchema\Command;

// Поле класса таблицы откуда брать тип
class ReferenceTo extends Base
{
    protected string $value;
    // Конструктор
    public function __construct(string $classname, string $fieldname)
    {
        $this->value = $classname . '.' . $fieldname;
    }
    // Получить значение
    public function value(): string
    {
        return $this->value;
    }
};
