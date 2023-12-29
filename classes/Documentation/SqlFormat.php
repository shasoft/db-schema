<?php

namespace Shasoft\DbSchema\Documentation;

use PhpMyAdmin\SqlParser\Utils\Formatter;

// Форматирование SQL
class SqlFormat
{
    // Форматирование SQL для вывода в html
    static public function html(string $sql): string
    {
        // Отформатировать
        $ret = Formatter::format($sql, ['type' => 'html']);
        // Заменить классы на стили
        $ret = preg_replace_callback(
            '/class=\"(.+?)\"/',
            function ($m) {
                $styles = [
                    'sql-reserved'  => 'font-weight: bold;color:blue',
                    'sql-variable'  => 'font-weight: bold;color:#908800',
                    'sql-number'  => 'font-weight: bold;color:#C00000',
                    'sql-comment' => 'font-weight: bold;color:green',
                    'sql-keyword' => 'font-weight: bold;color:purple',
                    'sql-atom' => 'font-weight: bold;color:red',
                    'sql-string' => 'font-weight: bold;color:sienna',
                    'sql-parameter' => 'font-weight: bold;color:red',
                ];
                if (!array_key_exists($m[1], $styles)) {
                    throw new \Exception('Параметр [' . $m[1] . '] не известен ');
                }
                $style = $styles[$m[1]];
                return 'style="' . $style . '"';
            },
            $ret
        );
        // Вернуть отформатированный результат
        return $ret;
    }
    // Форматирование SQL для консоли
    static public function console(string $sql): string
    {
        return Formatter::format($sql);
    }
};
