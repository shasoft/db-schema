<?php

namespace Shasoft\DbSchema\State;

use Shasoft\DbSchema\Command\Id;
use Shasoft\DbSchema\Command\Type;
use Shasoft\DbSchema\Command\Columns;

// Состояние индекса базы данных
class StateIndex extends StateTableChild
{
    // Список полей ключа
    public function columns(): array
    {
        return $this->value(Columns::class);
    }
    // Имя
    public function name(): string
    {
        return $this->value(Id::class);
    }
    // Индекс является уникальным?
    public function hasUnique(): bool
    {
        // Имя класса типа индекса
        $classname = $this->value(Type::class);
        //s_dd($classname);
        return (new $classname)->hasUnique();
    }
};
