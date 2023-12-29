<?php

namespace Shasoft\DbSchema\Command;

// Тип параметра PDO драйвера
// https://www.php.net/manual/ru/pdo.constants.php
class PdoParam extends Base
{
    // Конструктор
    public function __construct(protected int $value)
    {
    }
    // Получить значение
    public function value(): int
    {
        return $this->value;
    }
    // Получить значение в виде HTML
    public function valueHtml(): string
    {
        switch ($this->value) {
            case \PDO::PARAM_BOOL:
                $ret = 'PDO::PARAM_BOOL';
                break;
            case \PDO::PARAM_NULL:
                $ret = 'PDO::PARAM_NULL';
                break;
            case \PDO::PARAM_INT:
                $ret = 'PDO::PARAM_INT';
                break;
            case \PDO::PARAM_STR:
                $ret = 'PDO::PARAM_STR';
                break;
            case \PDO::PARAM_STR_NATL:
                $ret = 'PDO::PARAM_STR_NATL';
                break;
            case \PDO::PARAM_STR_CHAR:
                $ret = 'PDO::PARAM_STR_CHAR';
                break;
            case \PDO::PARAM_LOB:
                $ret = 'PDO::PARAM_LOB';
                break;
            case \PDO::PARAM_STMT:
                $ret = 'PDO::PARAM_STMT';
                break;
            case \PDO::PARAM_INPUT_OUTPUT:
                $ret = 'PDO::PARAM_INPUT_OUTPUT';
                break;
            default:
                $ret = 'PDO::???';
        }
        return '<span style="color:CadetBlue">' . $ret . '</span>';
    }
};
