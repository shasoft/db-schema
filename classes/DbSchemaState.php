<?php

namespace Shasoft\DbSchema;

use Shasoft\DbSchema\Table\Table;
use Shasoft\DbSchema\DbSchemaReflection;
use Shasoft\DbSchema\Command\Drop;
use Shasoft\DbSchema\Command\Type;
use Shasoft\DbSchema\Command\Migration;
use Shasoft\DbSchema\Command\Create;
use Shasoft\DbSchema\Command\Title;
use Shasoft\DbSchema\Command\ICommand;
use Shasoft\DbSchema\Command\Classname;
use Shasoft\DbSchema\Command\Custom;
use Shasoft\DbSchema\State\StateCommands;
use Shasoft\DbSchema\Command\RemoveCommand;
use Shasoft\DbSchema\Exceptions\DbSchemaExceptionCommandIsNotSupported;
use Shasoft\DbSchema\Exceptions\DbSchemaExceptionRequiredCommandNotDefined;
use Shasoft\DbSchema\Exceptions\DbSchemaExceptionTheDateTimeOfTheChangeWasIncorrectlyIndicatedEarlier;

// Состояние 
class DbSchemaState extends StateCommands
{
    // Значения
    protected array $values = [];
    // Конструктор
    public function __construct(array $commands = [])
    {
        parent::__construct($commands);
    }
    // Функция клонирования
    public function __clone()
    {
        $commands = [];
        foreach ($this->commands as $key => $command) {
            $commands[$key] = clone $command;
        }
        $this->commands = $commands;
    }
    // Установить команду
    public function setCommand(ICommand $command): void
    {
        if ($command instanceof RemoveCommand) {
            $refClass = new \ReflectionClass($command->value());
            while ($refClass) {
                if (!$refClass->isAbstract()) {
                    // Имя класса
                    $classname = $refClass->getName();
                    // Удалить команду
                    if (array_key_exists($classname, $this->commands)) {
                        unset($this->commands[$classname]);
                    }
                }
                // Перейти к родительскому классу
                $refClass = $refClass->getParentClass();
            }
        } else {
            //
            $refClass = new \ReflectionClass($command);
            while ($refClass) {
                if (!$refClass->isAbstract()) {
                    // Имя класса
                    $classname = $refClass->getName();
                    // Установить команду
                    $this->commands[$classname] = $command;
                }
                // Перейти к родительскому классу
                $refClass = $refClass->getParentClass();
            }
        }
    }
    // Удалить команду
    public function removeCommand(string $name): void
    {
        if (array_key_exists($name, $this->commands)) {
            unset($this->commands[$name]);
        }
    }
    // Добавить команду
    public function addCommand(ICommand $command): void
    {
        // Есть объект?
        if ($this->hasValue('object')) {
            // Добавление через объект (чтобы там отработали события)
            $object = $this->getValue('object');
            // Получить свойство объекта
            $refProperty = DbSchemaReflection::getObjectProperty($object, 'commands');
            // Обчистить список команд
            $refProperty->setValue($object, []);
            // Получить метод
            $refMethod = DbSchemaReflection::getObjectMethod($object, 'setCommand');
            // Записать текущие команды
            foreach ($this->commands() as $_command) {
                $refMethod->invoke($object, $_command, false);
            }
            // Сохранить список текущих команд
            $commandIds = array_flip(array_map(function ($_command) {
                return spl_object_id($_command);
            }, DbSchemaReflection::getObjectPropertyValue($object, 'commands', [])));
            // Добавить текущую команду
            $refMethod->invoke($object, $command, false);
            // Команды
            $commands = DbSchemaReflection::getObjectPropertyValue($object, 'commands', []);
            // Если появились новые команды в объекте, то их добавить в список
            foreach (DbSchemaReflection::getObjectPropertyValue($object, 'commands', []) as $_command) {
                if (!array_key_exists(spl_object_id($_command), $commandIds)) {
                    $this->commands[]  = $_command;
                }
            }
        } else {
            // Простое добавление
            $this->commands[]  = $command;
        }
        // Это команда смены типа?
        if ($command instanceof Type || $command instanceof Classname) {
            // Имя класса
            if ($command instanceof Classname) {
                $classname = Table::class;
            } else {
                $classname = $command->value();
            }
            //
            $object = new $classname;
            $this->setValue('object', $object);
            // Команды объекта
            $commands = DbSchemaReflection::getObjectPropertyValue($object, 'commands', []);
            // Команды из атрибутов
            foreach ($commands as $name => $_command) {
                // Установить команду
                $this->addCommand($_command);
            }
        }
        if ($command instanceof Migration) {
            // Если указана дата
            if ($this->hasValue('datetime')) {
                // то сравнить что время нового  изменения не раньше старого
                if (strcmp($this->getValue('datetime'), $command->value()) > 0) {
                    // 
                    throw new DbSchemaExceptionTheDateTimeOfTheChangeWasIncorrectlyIndicatedEarlier(
                        $this->getValue('tableClass'),
                        $this->getValue('datetime'),
                        $command->value()
                    );
                }
            }
            // Установить новое значение
            $this->setValue('datetime', $command->value());
        }
    }
    // Установить команды из объекта класса
    protected function setCommandFromClassname(string $classname): void
    {
        // Команды объекта
        $commands = DbSchemaReflection::getObjectPropertyValue(new $classname, 'commands', null);
        // Команды из атрибутов
        foreach ($commands as $name => $command) {
            // Установить команду
            $this->setCommand($command);
        }
    }
    // Установить время изменения  из атрибутов
    public function setDatetime(array $attributes, string $default): string
    {
        if (!empty($attributes)) {
            if ($attributes[0]->getName() == Migration::class) {
                // Создать команду
                $ret = new Migration(...$attributes[0]->getArguments());
            }
        }
        // Если не установлено
        if (!isset($ret)) {
            // Установить время изменения по умолчанию
            $ret = new Migration($default);
        }
        // Добавить
        $this->addCommand($ret);
        // Вернуть значение даты
        return $ret->value();
    }
    // Установить значение
    public function setValue(string $name, mixed $value): void
    {
        $this->values[$name] = $value;
    }
    // Получить значение
    public function getValue(string $name): mixed
    {
        return $this->values[$name];
    }
    // Есть значение?
    public function hasValue(string $name): bool
    {
        return array_key_exists($name, $this->values);
    }
    // Добавить команды из атрибутов
    public function addCommandFromAttributes(array $attributes, string $classname): void
    {
        // Получить список поддерживаемых команд
        $supportCommands = DbSchemaReflection::getObjectPropertyValue(new $classname, 'supportCommands', null);
        // Команды из атрибутов
        foreach ($attributes as $attribute) {
            // Класс команды
            $commandClass = $attribute->getName();
            // Команда поддерживается?
            $hasSupport = is_subclass_of($commandClass, Custom::class);
            if (!$hasSupport) {
                foreach ($supportCommands as $checkCommandClass => $_) {
                    $hasSupport = is_a($commandClass, $checkCommandClass) || $commandClass == $checkCommandClass;
                    if ($hasSupport) break;
                }
            }
            if (!$hasSupport) {
                throw new DbSchemaExceptionCommandIsNotSupported($commandClass, $classname);
            }
            // Установить команду
            $this->addCommand(new $commandClass(...$attribute->getArguments()));
        }
    }
    // Записать команды, которые меньше-равны указанного datetime
    public function writeCommands(string $datetime): ?static
    {
        $ret = new static;
        foreach ($this->commands as $_command) {
            if ($_command instanceof Migration) {
                if (strcmp($_command->value(), $datetime) > 0) {
                    break;
                }
            } else {
                $ret->setCommand($_command);
            }
        }
        // Если список команд пустой
        if (empty($ret->commands())) {
            // то вернуть NULL
            $ret = null;
        } else {
            // Выполнить проверку обязательных команд
            DbSchemaExceptionRequiredCommandNotDefined::check($ret, $this->getValue('tableClass'));
        }
        //
        return $ret;
    }
};
