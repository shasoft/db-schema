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

#[Comment('Таблица для примера')]
class TabExample3
{
    #[Comment('Идентификатор')]
    protected ColumnId $id;
    #[Comment('Имя')]
    #[Migration('2023-12-28T22:10:00+03:00')]
    #[Drop]
    protected ColumnString $name;
    #[Migration('2023-12-28T22:00:00+03:00')]
    #[Comment('Фамилия')]
    #[Migration('2023-12-28T22:10:00+03:00')]
    #[Name('surname')]
    protected ColumnString $fam;
    // Первичный ключ
    #[Columns('id')]
    protected IndexPrimary $pkKey;
}
