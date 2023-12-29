<?php

namespace Shasoft\DbSchema;

use Shasoft\DbSchema\Command\Base;
use Shasoft\DbSchema\State\StateCommands;

// Список изменений между командами
class DbSchemaCommandsChanges
{
    // Добавленные команды
    protected array $add = [];
    // Измененные команды
    protected array $change = [];
    // Удаленные команды
    protected array $drop = [];
    // Конструктор
    public function __construct(StateCommands $stateCommands1, StateCommands $stateCommands2)
    {
        $commands1 = $stateCommands1->commands();
        $commands2 = $stateCommands2->commands();
        // Сравнить команды 1 с 2
        foreach ($commands1 as $name => $command1) {
            if (array_key_exists($name, $commands2)) {
                $command2 = $commands2[$name];
                // А может это один и тот же объект?
                if (spl_object_id($command1) != spl_object_id($command2)) {
                    // Если метода value нет,  то команды на различия можно не проверять
                    if (method_exists($command1, 'value')) {
                        $a = $command1->value();
                        $b = $command2->value();
                        if (is_array($a)) {
                            $a = serialize($a);
                            $b = serialize($b);
                        }
                        if ($a != $b) {
                            // Команда изменилась
                            $this->change[$name] = $command2;
                        }
                    }
                }
            } else {
                // Команда name была удалена из состояния 
                $this->drop[$name] = $command1;
            }
        }
        // Сравнить команды 2 с 1
        foreach ($commands2 as $name => $command2) {
            if (!array_key_exists($name, $commands1)) {
                // Появилась новая команда
                $this->add[$name] = $command2;
            }
        }
    }
    // Количество изменений
    public function count(): int
    {
        return count($this->add) + count($this->change) + count($this->drop);
    }
    // Изменения есть?
    public function empty(): bool
    {
        return empty($this->add) && empty($this->change) && empty($this->drop);
    }
    // Проверить наличие команды
    public function has(string $name, ?string $type = null): bool
    {
        if (is_null($type)) {
            return
                array_key_exists($name,  $this->add) ||
                array_key_exists($name,  $this->change) ||
                array_key_exists($name,  $this->drop);
        }
        return array_key_exists($name,  $this->$type);
    }
    // Получить команду
    public function get(string $name): mixed
    {
        return $this->add[$name] ?? ($this->change[$name] ?? ($this->drop[$name] ?? null));
    }
};
