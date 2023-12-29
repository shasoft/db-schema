<?php

namespace Shasoft\DbSchema\Index;

use Shasoft\DbSchema\Index\Index;
use Shasoft\DbSchema\Command\Comment;

#[Comment('Уникальный индекс')]
class IndexUnique extends Index
{
    // Индекс является уникальным?
    public function hasUnique(): bool
    {
        return true;
    }
};
