<?php

namespace Shasoft\DbSchema\Tests\Table;

use Shasoft\DbSchema\Command\Drop;
use Shasoft\DbSchema\Command\Enum;
use Shasoft\DbSchema\Command\Name;
use Shasoft\DbSchema\Command\Type;
use Shasoft\DbSchema\Command\Migration;
use Shasoft\DbSchema\Command\Create;
use Shasoft\DbSchema\Index\IndexKey;
use Shasoft\DbSchema\Column\ColumnId;
use Shasoft\DbSchema\Command\Columns;
use Shasoft\DbSchema\Command\Comment;
use Shasoft\DbSchema\Command\TypeFrom;
use Shasoft\DbSchema\Tests\EnumString;
use Shasoft\DbSchema\Column\ColumnEnum;
use Shasoft\DbSchema\Column\ColumnReal;
use Shasoft\DbSchema\Command\MaxLength;
use Shasoft\DbSchema\Index\IndexUnique;
use Shasoft\DbSchema\Column\ColumnRefId;
use Shasoft\DbSchema\Index\IndexPrimary;
use Shasoft\DbSchema\Column\ColumnBinary;
use Shasoft\DbSchema\Column\ColumnString;
use Shasoft\DbSchema\Command\ReferenceTo;
use Shasoft\DbSchema\Reference\Reference;
use Shasoft\DbSchema\Column\ColumnBoolean;
use Shasoft\DbSchema\Column\ColumnInteger;
use Shasoft\DbSchema\Command\DefaultValue;
use Shasoft\DbSchema\Column\ColumnTypeFrom;
use Shasoft\DbSchema\Command\AutoIncrement;

#[Comment('Тестовая таблица')]
//#[Migration('2011-11-11T12:00:00+03:00')]
//#[Comment('Таблица для тестов')]
class TabTest
{
    //#[AutoIncrement]
    protected ColumnInteger $id;
    //#[Migration('2011-11-11T12:00:00+03:00')]
    //protected ColumnInteger $id2;
    #[ReferenceTo(self::class, 'refId')]
    protected Reference $refId2;
    #[ReferenceTo(self::class, 'id')]
    //#[Migration('2011-11-11T12:00:00+03:00')]
    //#[Comment('Ссылка на поле')]
    protected Reference $refId;
}
