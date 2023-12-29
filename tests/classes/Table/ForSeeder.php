<?php

namespace Shasoft\DbSchema\Tests\Table;

use Shasoft\DbSchema\Command\Enum;
use Shasoft\DbSchema\Tests\EnumInt;
use Shasoft\DbSchema\Column\ColumnId;
use Shasoft\DbSchema\Command\Columns;
use Shasoft\DbSchema\Command\Comment;
use Shasoft\DbSchema\Command\MaxValue;
use Shasoft\DbSchema\Command\MinValue;
use Shasoft\DbSchema\Command\Variable;
use Shasoft\DbSchema\Tests\EnumString;
use Shasoft\DbSchema\Column\ColumnEnum;
use Shasoft\DbSchema\Column\ColumnJson;
use Shasoft\DbSchema\Column\ColumnReal;
use Shasoft\DbSchema\Command\MaxLength;
use Shasoft\DbSchema\Tests\EnumDefault;
use Shasoft\DbSchema\Column\ColumnRefId;
use Shasoft\DbSchema\Index\IndexPrimary;
use Shasoft\DbSchema\Column\ColumnBinary;
use Shasoft\DbSchema\Column\ColumnString;
use Shasoft\DbSchema\Column\ColumnBoolean;
use Shasoft\DbSchema\Column\ColumnDecimal;
use Shasoft\DbSchema\Column\ColumnInteger;
use Shasoft\DbSchema\Column\ColumnDatetime;

#[Comment('Таблица для генерации данных')]
class ForSeeder
{
    #[MinValue(0)]
    #[MaxValue(2 ** 8 - 1)]
    protected ColumnInteger $integer8;
    #[MinValue(0)]
    #[MaxValue(2 ** 16 - 1)]
    protected ColumnInteger $integer16;
    #[MinValue(0)]
    #[MaxValue(2 ** 24 - 1)]
    protected ColumnInteger $integer24;
    #[MinValue(0)]
    #[MaxValue(2 ** 32 - 1)]
    protected ColumnInteger $integer32;
    #[MinValue(0)]
    #[MaxValue(2 ** 48 - 1)]
    protected ColumnInteger $integer48;
    #[MinValue(0)]
    #[MaxValue(2 ** 64 - 1)]
    protected ColumnInteger $integer64;
    #[MinValue(0)]
    #[MaxValue(2 ** 8 - 1)]
    protected ColumnId $id8;
    /*
    #[MinValue(0)]
    #[MaxValue(2 ** 16 - 1)]
    protected ColumnId $id16;
    #[MinValue(0)]
    #[MaxValue(2 ** 24 - 1)]
    protected ColumnId $id24;
    #[MinValue(0)]
    #[MaxValue(2 ** 32 - 1)]
    protected ColumnId $id32;
    #[MinValue(0)]
    #[MaxValue(2 ** 48 - 1)]
    protected ColumnId $id48;
    #[MinValue(0)]
    #[MaxValue(2 ** 64 - 1)]
    protected ColumnId $id64;
    //*/
    #[MinValue(0)]
    #[MaxValue(3.4028E+38)]
    protected ColumnReal $real32;
    #[MinValue(0)]
    #[MaxValue(3.4028E+380)]
    protected ColumnReal $real64;
    protected ColumnBoolean $boolean;
    #[MaxLength(10)]
    protected ColumnString $string10;
    #[MaxLength(32)]
    protected ColumnString $string32;
    protected ColumnRefId $refId;
    #[MaxLength(255)]
    protected ColumnJson $json255;
    protected ColumnDecimal $decimal;
    protected ColumnDatetime $datetime;
    #[MaxLength(8)]
    protected ColumnBinary $binary;
    #[Enum(EnumDefault::class)]
    protected ColumnEnum $enumDefault;
    #[Enum(EnumInt::class)]
    protected ColumnEnum $enumInt;
    #[Enum(EnumString::class)]
    protected ColumnEnum $enumString;

    #[Columns('id8')]
    protected IndexPrimary $pkKey;
}
