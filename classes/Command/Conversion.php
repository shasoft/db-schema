<?php

namespace Shasoft\DbSchema\Command;

// Преобразовать
abstract class Conversion extends Call
{
    // Преобразовать
    public function convert(mixed $value): mixed
    {
        return $this->call($value);
    }
    // Получить значение в виде HTML
    public function valueHtml(): string
    {
        $tmp = explode("\\", $this->value());
        $name = array_pop($tmp);

        return '<span data-bs-toggle="tooltip" title="' . addslashes($this->value()) . '" style="color:DarkCyan">' . $name . '</span>';
    }
};
