<?php

namespace Shasoft\DbSchema\Tests\Table;

use Shasoft\DbSchema\Command\Enum;
use Shasoft\DbSchema\Tests\EnumInt;
use Shasoft\DbSchema\Index\IndexKey;
use Shasoft\DbSchema\Column\ColumnId;
use Shasoft\DbSchema\Command\Columns;
use Shasoft\DbSchema\Command\Comment;
use Shasoft\DbSchema\Command\MaxValue;
use Shasoft\DbSchema\Command\MinValue;
use Shasoft\DbSchema\Command\Variable;
use Shasoft\DbSchema\Tests\EnumString;
use Shasoft\DbSchema\Column\ColumnBinary;
use Shasoft\DbSchema\Column\ColumnEnum;
use Shasoft\DbSchema\Column\ColumnJson;
use Shasoft\DbSchema\Column\ColumnReal;
use Shasoft\DbSchema\Command\MaxLength;
use Shasoft\DbSchema\Index\IndexUnique;
use Shasoft\DbSchema\Tests\EnumDefault;
use Shasoft\DbSchema\Column\ColumnRefId;
use Shasoft\DbSchema\Index\IndexPrimary;
use Shasoft\DbSchema\Column\ColumnString;
use Shasoft\DbSchema\Column\ColumnBoolean;
use Shasoft\DbSchema\Column\ColumnDecimal;
use Shasoft\DbSchema\Column\ColumnInteger;

#[Comment('Таблица всех индексов')]
class AllIndexes
{
    // Поля
    protected ColumnInteger $f1;
    protected ColumnInteger $f2;
    protected ColumnInteger $f3;
    protected ColumnInteger $f4;
    //
    #[Columns('f1')]
    protected IndexPrimary $i1;
    #[Columns('f1:asc')]
    protected IndexKey $i2;
    #[Columns('f1:desc')]
    protected IndexUnique $i3;
}
