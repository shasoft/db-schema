<?php

namespace Shasoft\DbSchema\Column;

use Shasoft\DbSchema\Command\Seeder;
use Shasoft\DbSchema\Command\Comment;
use Shasoft\DbSchema\Command\Variable;
use Shasoft\DbSchema\Command\MaxLength;
use Shasoft\DbSchema\Column\ColumnString;
use Shasoft\DbSchema\Command\DefaultValue;
use Shasoft\DbSchema\Command\ConversionInput;
use Shasoft\DbSchema\Command\ConversionOutput;

// Json данные
class ColumnJson extends ColumnString
{
    // Конструктор
    public function __construct()
    {
        // Вызвать конструктор родителя
        parent::__construct();
        // Удалить команды
        $this->removeCommand(Seeder::class);
        // Установить команды
        $this->setCommand(new Comment('Json данные'));
        $this->setCommand(new MaxLength(256 * 256 - 1));
        $this->setCommand(new DefaultValue());
        $this->setCommand(new ConversionInput(self::class . '::inputJson'), false);
        $this->setCommand(new ConversionOutput(self::class . '::outputJson'), false);
        // Удалить команды из списка поддерживаемых
        $this->removeSupportCommand(Variable::class);
    }
    // PHP=>БД
    public static function inputJson(array|null $value): ?string
    {
        if (is_null($value)) {
            return null;
        }
        return is_array($value) ? (json_encode($value, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE)) : '{}';
    }
    // БД=>PHP
    public static function outputJson(string|null $value): ?array
    {
        if (is_null($value)) {
            return null;
        }
        $ret = [];
        if (!empty($value) && is_string($value)) {
            $ret = json_decode($value, true);
        }
        return $ret;
    }
};
