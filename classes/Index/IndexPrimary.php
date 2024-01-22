<?php

namespace Shasoft\DbSchema\Index;

use Shasoft\DbSchema\Index\Index;
use Shasoft\DbSchema\Command\Title;

#[Title('Первичный ключ (индекс)')]
class IndexPrimary extends Index
{
    // Индекс является уникальным?
    public function hasUnique(): bool
    {
        return true;
    }
};
