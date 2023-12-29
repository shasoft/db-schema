<?php

namespace Shasoft\DbSchema\Tests\Table;

use Shasoft\DbSchema\Command\Drop;
use Shasoft\DbSchema\Command\Name;
use Shasoft\DbSchema\Command\Type;
use Shasoft\DbSchema\Command\Migration;
use Shasoft\DbSchema\Command\Create;
use Shasoft\DbSchema\Index\IndexKey;
use Shasoft\DbSchema\Column\ColumnId;
use Shasoft\DbSchema\Command\Columns;
use Shasoft\DbSchema\Command\Comment;
use Shasoft\DbSchema\Command\MaxValue;
use Shasoft\DbSchema\Command\MinValue;
use Shasoft\DbSchema\Column\ColumnReal;
use Shasoft\DbSchema\Command\MaxLength;
use Shasoft\DbSchema\Index\IndexPrimary;
use Shasoft\DbSchema\Column\ColumnString;
use Shasoft\DbSchema\Column\ColumnBoolean;
use Shasoft\DbSchema\Command\DefaultValue;

// Комментарий таблицы
#[Comment('Таблица для примера')]
class TabExample1
{
    #[Comment('Идентификатор')]
    protected ColumnId $id;
    #[Comment('Имя')]
    protected ColumnString $name;
    // Первичный ключ
    #[Columns('id')]
    protected IndexPrimary $pkKey;
}
