<?php

namespace Shasoft\DbSchema;

use Shasoft\DbSchema\Command\Drop;
use Shasoft\DbSchema\Command\Migration;
use Shasoft\DbSchema\Command\Create;
use Shasoft\DbSchema\Command\Comment;
use Shasoft\DbSchema\Command\ICommand;
use Shasoft\DbSchema\Command\RemoveCommand;

// Контейнер с командами
abstract class DbSchemaCommands
{
    // Префикс метода обработки событий
    static private string $eventNamePrefix = 'onCommand';
    // Все команды
    private array $commands = [];
    // Поддерживаемые команды
    private array $supportCommands = [];
    // Обязательные команды
    private array $requiredCommands = [];
    // События обработки команд
    private array $events = [];
    // Конструктор
    public function __construct()
    {
        //
        $refClass = new \ReflectionClass($this);
        $methods = $refClass->getMethods(\ReflectionMethod::IS_PROTECTED);
        foreach ($methods as $method) {
            if (!$method->isStatic()) {
                if (substr($method->getName(), 0, strlen(self::$eventNamePrefix)) == self::$eventNamePrefix) {
                    $types = array_map(function (\ReflectionParameter $arg) {
                        if ($arg->hasType()) {
                            return (string)$arg->getType();
                        }
                    }, $method->getParameters());
                    foreach ($types as $typeName) {
                        if (!array_key_exists($typeName, $this->events)) {
                            $this->events[$typeName] = [];
                        }
                        $this->events[$typeName][$method->getName()] = $types;
                    }
                }
            }
        }
        // Поддерживаемые команды
        $this->addSupportCommand([
            Migration::class,
            Comment::class,
            Drop::class,
            Create::class
        ]);
    }
    // Обработка команды Create
    protected function onCommand_Create(Create $create): void
    {
        $this->setCommand(new RemoveCommand(Drop::class));
    }
    // Обработка команды Drop
    protected function onCommand_Drop(Drop $drop): void
    {
        $this->setCommand(new RemoveCommand(Create::class));
    }
    // Установить обязательную команду
    protected function addRequiredCommand(string|array $name): static
    {
        if (is_array($name)) {
            foreach ($name as $_name)
                $this->addRequiredCommand($_name);
        } else {
            $this->requiredCommands[$name] = 1;
            // Установить обязательную команду в поддерживаемые
            $this->addSupportCommand($name);
        }
        // Вернуть указатель на себя
        return $this;
    }
    // Установить поддерживаемую команду
    protected function addSupportCommand(string|array $name): static
    {
        if (is_array($name)) {
            foreach ($name as $_name)
                $this->addSupportCommand($_name);
        } else {
            $this->supportCommands[$name] = 1;
        }
        // Вернуть указатель на себя
        return $this;
    }
    // Удалить поддерживаемую команду
    protected function removeSupportCommand(string|array $name): static
    {
        if (is_array($name)) {
            foreach ($name as $_name)
                $this->removeSupportCommand($_name);
        } else {
            if (array_key_exists($name, $this->supportCommands)) {
                unset($this->supportCommands[$name]);
            }
        }
        // Вернуть указатель на себя
        return $this;
    }
    // Удалить команду
    protected function removeCommand(string $name): static
    {
        if (array_key_exists($name, $this->commands)) {
            unset($this->commands[$name]);
        }
        // Вернуть указатель на себя
        return $this;
    }
    // Установить команду
    protected function setCommand(ICommand $command, bool $hasSupport = true): static
    {
        // Установить команду в поддерживаемые
        if ($hasSupport) {
            $this->supportCommands[get_class($command)] = 1;
        }
        // 
        $refClass = new \ReflectionClass($command);
        // Записать команду
        while ($refClass) {
            // Имя класса
            $classname = $refClass->getName();
            // Установить команду
            if (!$refClass->isAbstract()) {
                $this->commands[$classname] = $command;
            }
            // Проверить наличие события для команды
            if (array_key_exists($classname, $this->events)) {
                // Получить событие
                $event = $this->events[$classname];
                // Перебрать все методы
                foreach ($event as $commandName => $commandArgs) {
                    // Получить список команд
                    $args = [];
                    foreach ($commandArgs as $name) {
                        if (array_key_exists($name, $this->commands)) {
                            $args[] = $this->commands[$name];
                        }
                    }
                    // Если в наличии все зависимые команды
                    if (count($args) == count($commandArgs)) {
                        call_user_func_array([$this, $commandName], $args);
                    }
                }
            }
            // Перейти к родителю
            $refClass = $refClass->getParentClass();
        }
        // Вернуть указатель на себя
        return $this;
    }
}
