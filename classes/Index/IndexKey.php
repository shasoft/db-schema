<?php

namespace Shasoft\DbSchema\Index;

use Shasoft\DbSchema\Index\Index;
use Shasoft\DbSchema\Command\Comment;

#[Comment('Неуникальный индекс')]
class IndexKey extends Index
{
    // Индекс является уникальным?
    public function hasUnique(): bool
    {
        return false;
    }
};
