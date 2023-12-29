<?php

namespace Shasoft\DbSchema\Tests\Table;

use Shasoft\DbSchema\Command\Drop;
use Shasoft\DbSchema\Command\Name;
use Shasoft\DbSchema\Command\Migration;
use Shasoft\DbSchema\Column\ColumnId;
use Shasoft\DbSchema\Command\Columns;
use Shasoft\DbSchema\Command\Comment;
use Shasoft\DbSchema\Column\ColumnText;
use Shasoft\DbSchema\Command\RelNameTo;
use Shasoft\DbSchema\Tests\Table\User0;
use Shasoft\DbSchema\Column\ColumnRefId;
use Shasoft\DbSchema\Command\RelTableTo;
use Shasoft\DbSchema\Index\IndexPrimary;
use Shasoft\DbSchema\Column\ColumnString;
use Shasoft\DbSchema\Command\ReferenceTo;
use Shasoft\DbSchema\Reference\Reference;
use Shasoft\DbSchema\Column\ColumnInteger;
use Shasoft\DbSchema\Command\DbSchemaType;
use Shasoft\DbSchema\Column\ColumnDatetime;
use Shasoft\DbSchema\Relation\RelationManyToOne;
use Shasoft\DbSchema\Relation\RelationOneToMany;

//#[Migration('2010-01-18T12:00:00+03:00')]
#[Comment('Статьи')]
class Article
{
    //
    #[Comment('Идентификатор')]
    protected ColumnId $id;
    #[Comment('Ссылка на автора')]
    #[ReferenceTo(User::class, 'id')]
    protected Reference $userId;
    #[Comment('Название')]
    protected ColumnString $title;
    #[Columns('id')]
    protected IndexPrimary $pkId;
    // Отношение
    #[RelTableTo(User::class)]
    #[RelNameTo('articles')]
    #[Columns(['userId' => 'id'])]
    #[Comment('Автор')]
    protected RelationManyToOne $author;
}
