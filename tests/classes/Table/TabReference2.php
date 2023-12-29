<?php

namespace Shasoft\DbSchema\Tests\Table;

use Shasoft\DbSchema\Command\Name;
use Shasoft\DbSchema\Command\Migration;
use Shasoft\DbSchema\Command\Comment;
use Shasoft\DbSchema\Command\ReferenceTo;
use Shasoft\DbSchema\Reference\Reference;

#[Comment('Тестовая таблица 2')]
class TabReference2
{
    #[ReferenceTo(TabReference1::class, 'id')]
    protected Reference $refId;
    #[ReferenceTo(self::class, 'refId')]
    protected Reference $refId2;
    #[ReferenceTo(self::class, 'refId2')]
    #[Migration('2011-12-11T12:00:00+03:00')]
    #[Name('ReferenceToRefId2')]
    protected Reference $refId3;
}
