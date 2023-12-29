<?php

namespace Shasoft\DbSchema\Tests\Table;

use Shasoft\DbSchema\Command\Drop;
use Shasoft\DbSchema\Command\Name;
use Shasoft\DbSchema\Command\Type;
use Shasoft\DbSchema\Command\Create;
use Shasoft\DbSchema\Index\IndexKey;
use Shasoft\DbSchema\Column\ColumnId;
use Shasoft\DbSchema\Command\Columns;
use Shasoft\DbSchema\Command\Comment;
use Shasoft\DbSchema\Command\MaxValue;
use Shasoft\DbSchema\Command\MinValue;
use Shasoft\DbSchema\Column\ColumnReal;
use Shasoft\DbSchema\Command\MaxLength;
use Shasoft\DbSchema\Command\Migration;
use Shasoft\DbSchema\Index\IndexPrimary;
use Shasoft\DbSchema\Column\ColumnString;
use Shasoft\DbSchema\Column\ColumnBoolean;
use Shasoft\DbSchema\Column\ColumnInteger;
use Shasoft\DbSchema\Command\DefaultValue;

#[Comment('Таблица для примера')]
class TabExample4
{
    #[Comment('Рост человека, мм')]
    #[MinValue(0)]
    #[MaxValue(4009)]
    protected ColumnInteger $rost;
}
