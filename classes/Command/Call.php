<?php

namespace Shasoft\DbSchema\Command;

// Функция
abstract class Call extends Base
{
    // Конструктор
    public function __construct(protected ?string $value)
    {
    }
    // Получить значение
    public function value(): ?string
    {
        return $this->value;
    }
    // Вызвать функцию
    public function call(...$args): mixed
    {
        if (!is_null($this->value)) {
            // Добавить родительский узел
            $args[] = $this->parent;
            // Вызвать функцию
            return call_user_func_array($this->value, $args);
        }
        return null;
    }
    // Получить значение в виде HTML
    public function valueHtml(): string
    {
        $tmp = explode("\\", $this->value());
        $name = array_pop($tmp);

        return '<span data-bs-toggle="tooltip" title="' . addslashes($this->value()) . '" style="color:DarkCyan">' . $name . '</span>';
    }
};
