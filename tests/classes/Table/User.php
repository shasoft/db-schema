<?php

namespace Shasoft\DbSchema\Tests\Table;

use Shasoft\DbSchema\Command\Drop;
use Shasoft\DbSchema\Command\Name;
use Shasoft\DbSchema\Command\Type;
use Shasoft\DbSchema\Command\Migration;
use Shasoft\DbSchema\Column\ColumnId;
use Shasoft\DbSchema\Command\Columns;
use Shasoft\DbSchema\Command\Comment;
use Shasoft\DbSchema\Column\ColumnText;
use Shasoft\DbSchema\Command\LimitText;
use Shasoft\DbSchema\Tests\Table\User0;
use Shasoft\DbSchema\Index\IndexPrimary;
use Shasoft\DbSchema\Column\ColumnString;
use Shasoft\DbSchema\Column\ColumnInteger;
use Shasoft\DbSchema\Command\DbSchemaType;
use Shasoft\DbSchema\Column\ColumnDatetime;
use Shasoft\DbSchema\Column\RelationOneToMany;

//#[Migration('2010-01-18T12:00:00+03:00')]
#[Comment('Пользователи')]
class User
{
    //
    #[Comment('Идентификатор')]
    protected ColumnId $id;
    #[Comment('Имя')]
    protected ColumnString $name;
    #[Columns('id')]
    protected IndexPrimary $pkId;
}
