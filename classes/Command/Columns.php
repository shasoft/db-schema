<?php

namespace Shasoft\DbSchema\Command;

use Shasoft\DbSchema\DbSchemaDriver;

// Список колонок(полей)
class Columns extends Base
{
    protected array $columns;
    // Конструктор
    public function __construct(...$fields)
    {
        if (count($fields) > 0 && is_array($fields[0])) {
            $this->columns = $fields[0];
        } else {
            $this->columns = $fields;
        }
        $this->columns = array_map('trim', $this->columns);
    }
    // Получить список полей
    public function value(): array
    {
        return $this->columns;
    }
    // Получить список полей
    public function fields(DbSchemaDriver $driver): array
    {
        return array_map(function ($fieldname) use ($driver) {
            $tmpOrder = explode(':', $fieldname);
            $tmp = explode('(', $tmpOrder[0]);
            $ret = $driver->quote($tmp[0]);
            if (count($tmp) > 1) {
                $ret .= '(' . intval(trim($tmp[1])) . ')';
            }
            if (count($tmpOrder) > 1) {
                if (strtoupper($tmpOrder[1]) == 'DESC') {
                    $ret .= ' DESC';
                } else {
                    $ret .= ' ASC';
                }
            }
            return $ret;
        }, $this->value());
    }
};
