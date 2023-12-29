<?php

namespace Shasoft\DbSchema;

use Shasoft\DbSchema\Command\Comment;
use Shasoft\DbSchema\Column\ColumnJson;
use Shasoft\DbSchema\Command\MaxLength;
use Shasoft\DbSchema\Index\IndexPrimary;
use Shasoft\DbSchema\Column\ColumnString;
use Shasoft\DbSchema\Command\Columns;
use Shasoft\DbSchema\Column\ColumnInteger;

// Техническая таблица миграций
class DbSchemaTechTable
{
    #[Comment('Номер миграции')]
    protected ColumnInteger $num;
    #[Comment('Номер списка команд в миграции')]
    protected ColumnInteger $sub;
    #[Comment('Имя миграции')]
    #[MaxLength(30)]
    protected ColumnString $name;
    #[Comment('Имя класса таблицы')]
    #[MaxLength(255)]
    protected ColumnString $classname;
    #[Comment('Миграции перевода БД в следующее состояние')]
    protected ColumnJson $up;
    #[Comment('Миграции отмены состояния из up')]
    protected ColumnJson $down;
    //
    #[Columns('num', 'sub')]
    protected IndexPrimary $pkKey;
};
