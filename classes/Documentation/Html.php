<?php

namespace Shasoft\DbSchema\Documentation;

use Twig\TwigFilter;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

abstract class Html
{
    // Конструктор
    public function __construct(private string $filepathTemplate, private array $args = [])
    {
    }
    // Добавление функционала в Twig
    protected function onTwig(Environment $twig): void
    {
    }
    // Получить HTML
    public function __toString(): string
    {
        $loader = new FilesystemLoader(dirname($this->filepathTemplate));
        $twig = new Environment($loader, []);
        //
        // Получить пространство имен от полного имени класса
        $twig->addFilter(new TwigFilter('shortClass', function (string $classname) {
            // Разобрать имя класса
            $tmp = explode('\\', $classname);
            // Удалить имя класса
            return array_pop($tmp);
        }));
        // Получить пространство имен от полного имени класса
        $twig->addFilter(new TwigFilter('s_dump', function ($val) {
            return s_dump_html($val);
        }));
        // Сгенерировать имя
        $twig->addFilter(new TwigFilter('name', function ($val) {
            return hash('crc32', $val);
        }));
        // Сгенерировать значение команды
        $twig->addFilter(new TwigFilter('commandHtmlValue', function ($command) {
            return HtmlTypes::htmlValueCommand($command);
        }));

        // Вызвать пользовательский обработчик
        $this->onTwig($twig);
        //
        return $twig->render(basename($this->filepathTemplate), $this->args);
    }
};
