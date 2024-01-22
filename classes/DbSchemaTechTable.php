<?php

namespace Shasoft\DbSchema;

use Shasoft\DbSchema\Command\Title;
use Shasoft\DbSchema\Column\ColumnJson;
use Shasoft\DbSchema\Command\MaxLength;
use Shasoft\DbSchema\Index\IndexPrimary;
use Shasoft\DbSchema\Column\ColumnString;
use Shasoft\DbSchema\Command\Columns;
use Shasoft\DbSchema\Column\ColumnInteger;

// Техническая таблица миграций
class DbSchemaTechTable
{
    #[Title('Номер миграции')]
    protected ColumnInteger $num;
    #[Title('Номер списка команд в миграции')]
    protected ColumnInteger $sub;
    #[Title('Имя миграции')]
    #[MaxLength(30)]
    protected ColumnString $name;
    #[Title('Имя класса таблицы')]
    #[MaxLength(255)]
    protected ColumnString $classname;
    #[Title('Миграции перевода БД в следующее состояние')]
    protected ColumnJson $up;
    #[Title('Миграции отмены состояния из up')]
    protected ColumnJson $down;
    //
    #[Columns('num', 'sub')]
    protected IndexPrimary $pkKey;
};
