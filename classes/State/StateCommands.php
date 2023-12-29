<?php

namespace Shasoft\DbSchema\State;

use Shasoft\Reflection\Reflection;
use Shasoft\DbSchema\Command\ICommand;

// Состояние с командами
class StateCommands
{
    // Команды
    protected array $commands;
    // Конструктор
    public function __construct(array $commands = [])
    {
        $this->commands = $commands;
        // Установить родительский контейнер команд для каждой команды
        foreach ($commands as $command) {
            Reflection::getObjectProperty($command, 'parent')->setValue($command, $this);
        }
    }
    // Получить команду
    public function command(string $name): ICommand
    {
        return $this->commands[$name];
    }
    // Получить список команд
    public function commands(\Closure|array|null $cb = null): array
    {
        if (is_array($cb)) {
            $classes = [];
            foreach ($cb as $name) {
                $classes[$name] = 1;
            }
            return array_filter($this->commands, function (ICommand $command) use ($classes): bool {
                return array_key_exists(get_class($command), $classes);
            });
        } else if (is_callable($cb)) {
            return array_filter($this->commands, $cb);
        }
        return $this->commands;
    }
    // Проверить наличие команды
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->commands);
    }
    // Получить команду
    public function get(string $name): mixed
    {
        if (array_key_exists($name, $this->commands)) {
            return $this->commands[$name];
        }
        return null;
    }
    // Получить значение команды
    public function value(string $name, mixed $default = null): mixed
    {
        if (array_key_exists($name, $this->commands)) {
            $command = $this->commands[$name];
            if (method_exists($command, 'value')) {
                return $command->value();
            }
        }
        return $default;
    }
    // Вызвать функцию обратного вызова если команда существует
    public function valueHas(mixed &$param, string $name, \Closure $cb): void
    {
        if ($this->has($name)) {
            $cb($param, $this->value($name));
        }
    }
};
