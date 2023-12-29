<?php

namespace Shasoft\DbSchema\Documentation;

use Twig\Environment;
use Shasoft\Reflection\Items;
use Shasoft\DbSchema\Index\Index;
use Shasoft\DbSchema\Table\Table;
use Shasoft\DbSchema\Command\Base;
use Shasoft\Filesystem\Filesystem;
use Shasoft\Reflection\Reflection;
use Shasoft\DbSchema\Column\Column;
use Shasoft\DbSchema\Command\Comment;
use Shasoft\DbSchema\DbSchemaCommands;
use Shasoft\DbSchema\Relation\Relation;
use Shasoft\DbSchema\Reference\Reference;

class HtmlTypes extends Html
{
    // Конструктор
    public function __construct(array $types = [])
    {
        $types = array_map(function (string $classname) {
            return self::getTypeInfo($classname);
        }, $types);
        Filesystem::items(__DIR__ . '/../', function (\SplFileInfo $spi) use (&$types) {
            if ($spi->isFile()) {
                $items = Items::fileList($spi->getPathname());
                foreach ($items as $classname => $type) {
                    //
                    $refClass = new \ReflectionClass($classname);
                    if (!$refClass->isAbstract() && $refClass->isSubclassOf(DbSchemaCommands::class)) {
                        //
                        if ($type == 'class_exists') {
                            $types[] = self::getTypeInfo($classname);
                        }
                    }
                }
            }
        });
        $args = [
            'table' => [],
            'column' => [],
            'index' => [],
            'relation' => [],
            'reference' => []
        ];
        foreach ($types as $typeItem) {
            $args[$typeItem['type']][] = $typeItem;
        }
        $args = [
            'types' => [
                'Таблица' => $args['table'],
                'Типы колонок' => $args['column'],
                'Типы индексов' => $args['index'],
                'Типы отношений' => $args['relation'],
                'Ссылка на поле таблицы' => $args['reference']
            ]
        ];
        //
        parent::__construct(__DIR__ . '/../../twig/HtmlTypes.html.twig', $args);
    }
    // Значение команды
    static protected int $maxLen = 80;
    static public function htmlValueCommand(mixed $command): ?string
    {
        $ret = null;
        if (method_exists($command, 'valueHtml')) {
            $ret = $command->valueHtml();
        } else {
            if (method_exists($command, 'value')) {
                $ret = $command->value();
                $color = null;
                if (is_null($ret)) {
                    $color = 'red';
                    $ret = 'null';
                } else if (is_string($ret)) {
                    if (false === mb_detect_encoding((string)$ret, null, true)) {
                        $ret = 'binary[' . strlen($ret) . ']';
                        $color = '#56DB3A';
                    } else {
                        $color = 'green';
                    }
                    $len = mb_strlen($ret);
                    if ($len > self::$maxLen) {
                        $ret = '<span data-bs-toggle="tooltip" title="' . $ret . '" style="color:' . $color . '">' . mb_substr($ret, 0, self::$maxLen) . '...</span>';
                    }
                } else {
                    if (is_numeric($ret)) {
                        $color = '#1299DA';
                    } else if (is_bool($ret)) {
                        $color = '#FF8400';
                        $ret = json_encode($ret);
                    } else if (is_array($ret)) {
                        $color = '#6610f2';
                        $ret = json_encode($ret);
                        $len = mb_strlen($ret);
                        if ($len > self::$maxLen) {
                            $ret = '<span data-bs-toggle="tooltip" title="' . htmlentities($ret) . '" style="color:' . $color . '">' . mb_substr($ret, 0, self::$maxLen) . '...</span>';
                        }
                    } else {
                        $color = "red";
                    }
                }
                if (!is_null($color)) {
                    $ret = '<span style="color:' . $color . '">' . $ret . '</span>';
                }
            }
        }
        return $ret;
    }
    // Добавление функционала в Twig
    static public function getTypeInfo(string $classname): array
    {
        $object = new $classname();
        // Список обязательных команд
        $required = array_flip(array_keys(Reflection::getObjectPropertyValue($object, 'requiredCommands', [])));
        // Список поддерживаемых команд
        $support = array_flip(array_keys(Reflection::getObjectPropertyValue($object, 'supportCommands', [])));
        // Список текущих команд
        $commandsRaw = Reflection::getObjectPropertyValue($object, 'commands', []);
        //
        $commands = [];
        foreach ($commandsRaw as $name => $command) {
            $value = self::htmlValueCommand($command);
            //
            $commands[$name] = [
                'name' => $name,
                'value' => $value,
                'hasValue' => !is_null($value),
                'support' => array_key_exists($name, $support),
                'required' => array_key_exists($name, $required)
            ];
        }
        // Добавить поддерживаемые команды без значений
        foreach ($support as $name => $_) {
            if (!array_key_exists($name, $commands)) {
                $commands[$name] = [
                    'name' => $name,
                    'hasValue' => false,
                    'support' => array_key_exists($name, $support),
                    'required' => array_key_exists($name, $required)
                ];
            }
        }
        // Добавить поддерживаемые команды без значений
        foreach ($required as $name => $_) {
            if (!array_key_exists($name, $commands)) {
                $commands[$name] = [
                    'name' => $name,
                    'hasValue' => false,
                    'support' => array_key_exists($name, $support),
                    'required' => array_key_exists($name, $required)
                ];
            }
        }
        $commands = array_map(function (array $item) {
            if ($item['support']) {
                if ($item['required']) {
                    $item['color'] = 'red';
                    $item['num'] = 1;
                } else {
                    $item['color'] = 'black';
                    $item['num'] = 2;
                }
            } else {
                $item['color'] = 'gray';
                $item['num'] = 0;
            }
            return $item;
        }, array_values($commands));
        usort($commands, function (array $command1, array $command2) {
            if ($command1['num'] == $command2['num']) {
                return $command1['hasValue'] == $command2['hasValue'] ? strcmp($command1['name'], $command2['name']) : ($command1['hasValue'] ? -1 : 1);
            }
            return $command1['num'] < $command2['num'] ? -1 : 1;
        });
        //s_dd($commands);
        //
        if ($object instanceof Table) {
            $type = 'table';
        } else if ($object instanceof Column) {
            $type = 'column';
        } else if ($object instanceof Index) {
            $type = 'index';
        } else if ($object instanceof Relation) {
            $type = 'relation';
        } else if ($object instanceof Reference) {
            $type = 'reference';
        }
        // Описание
        if (array_key_exists(Comment::class, $commandsRaw)) {
            $comment = $commandsRaw[Comment::class]->value();
        } else {
            $refClass = new \ReflectionClass($classname);
            $attrs = $refClass->getAttributes(Comment::class);
            if (!empty($attrs)) {
                $comment = $attrs[0]->getArguments()[0];
            } else {
                $comment = '';
            }
        }
        //
        return [
            'object' => $object,
            'comment' => $comment,
            'type' => $type,
            'name' => $classname,
            'commands' => $commands
        ];
    }
    // Добавление функционала в Twig
    protected function onTwig(Environment $twig): void
    {
    }
};
