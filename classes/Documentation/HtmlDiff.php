<?php

namespace Shasoft\DbSchema\Documentation;

use Twig\TwigFilter;
use Twig\Environment;
use Shasoft\DbSchema\State\StateColumn;
use Shasoft\DbSchema\Documentation\SqlFormat;

class HtmlDiff extends Html
{
    // Конструктор
    public function __construct(array $migrations)
    {
        // Вызвать конструктор родителя
        parent::__construct(__DIR__ . '/../../twig/HtmlDiff.html.twig', [
            'migrations' => $migrations
        ]);
    }
    // Добавление функционала в Twig
    protected function onTwig(Environment $twig): void
    {
        // Форматирование SQL кода
        $twig->addFilter(new TwigFilter('sqlFormat', function ($sql) {
            return SqlFormat::html($sql);
        }));
        // Форматирование SQL кода
        $twig->addFilter(new TwigFilter('columnId', function (StateColumn $column) {
            return 'Z' . strtolower(hash('crc32', $column->table()->database()->name() . ':' . $column->table()->name() . ':' . $column->name()));
        }));
    }
};
