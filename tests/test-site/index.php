<?php

use Shasoft\Pdo\PdoLog;
use Shasoft\Pdo\SqlFormat;
use Shasoft\Filesystem\File;
use Shasoft\DbSchema\Command\TabName;
use Shasoft\DbSchema\Tests\Table\User;
use Shasoft\DbSchema\Column\ColumnReal;
use Shasoft\DbSchema\DbSchemaMigrations;
use Shasoft\DbSchema\Tests\Table\Article;
use Shasoft\DbSchema\Tests\Table\TabTest;
use Shasoft\DbSchema\DbSchemaStateManager;
use Shasoft\DbSchema\Tests\Table\AllTypes;
use Shasoft\DbSchema\Tests\Table\ForSeeder;
use Shasoft\DbSchema\Documentation\HtmlDiff;
use Shasoft\DbSchema\Tests\Table\AllChanges;
use Shasoft\DbSchema\Tests\Table\AllIndexes;
use Shasoft\DbSchema\Tests\Table\TabExample;
use Shasoft\DbSchema\Documentation\HtmlTypes;
use Shasoft\DbSchema\Tests\Table\TabExample1;
use Shasoft\DbSchema\Tests\Table\TabExample2;
use Shasoft\DbSchema\Tests\Table\TabExample3;
use Shasoft\DbSchema\Tests\Table\TabExample4;
use Shasoft\DbSchema\Tests\Table\TabExample5;
use Shasoft\DbSchema\Tests\Table\TabReference;
use Shasoft\Pdo\Connection\PdoConnectionMySql;
use Shasoft\DbSchema\Tests\Table\TabReference1;
use Shasoft\DbSchema\Tests\Table\TabReference2;
use Shasoft\DbSchema\Driver\DbSchemaDriverMySql;
use Shasoft\Pdo\Connection\PdoConnectionPostgreSql;
use Shasoft\DbSchema\Driver\DbSchemaDriverPostgreSql;

require_once __DIR__ . '/../classes/bootstrap.php';

//echo phpinfo();

/*
$a = 1.7817466E+38;
$b = 1.7817465947118E+38;
s_dd(
    ColumnReal::compare($a, $b),
    ColumnReal::compare(1.7817467E+38, 1.7817465947118E+38),
    ColumnReal::compare(1.7817466E+38, 1.7817465957118E+38)
);
//*/

s_dump_run(function () {
    // Создать соединение
    //*
    $pdoConnection = new PdoConnectionMySql([
        'dbname' => 'cmg-db-test',
        'host' => 'localhost',
        'username' => 'root'
    ]);
    //*/
    /*
    $pdoConnection = new PdoConnectionPostgreSql([
        'dbname' => 'cmg-db-test',
        'host' => 'localhost',
        'username' => 'postgres',
        'password' => '123'
    ]);
    //*/
    // Удалить всё из БД
    $pdoConnection->reset();
    // Получить миграции
    //xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY + XHPROF_FLAGS_NO_BUILTINS);
    $migrations = DbSchemaMigrations::get(
        [
            //Article::class, User::class,
            //TabTest::class,
            AllTypes::class,
            //AllChanges::class,
            //AllIndexes::class,
            //ForSeeder::class,
            //TabExample5::class,
            //TabReference1::class,
            //TabReference2::class
        ],
        DbSchemaDriverMySql::class
    );
    /*
    $xhprof_data = xhprof_disable();
    $XHPROF_ROOT = realpath('S:/Projects/PHP/XHProf');
    include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
    include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";

    // save raw data for this profiler run using default
    // implementation of iXHProfRuns.
    $xhprof_runs = new XHProfRuns_Default($XHPROF_ROOT . '/.save');
    // save the run under a namespace "xhprof_foo"
    $run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_foo");
    //*/
    // Создание экземпляра класса xhprof
    // Вывод для отладки
    //$migrations->dump();
    // Выполнить миграции
    $migrations->run($pdoConnection);

    // Отменить последнюю миграцию
    /*
    $rc = 1;
    while ($rc != 0) {
        $rc = $migrations->cancel($pdoConnection);
        s_dump($rc);
    }
    //*/
    // Сгенерировать данные
    if ($migrations->database()->hasTable(ForSeeder::class)) {
        $table = $migrations->database()->table(ForSeeder::class);
        $rows = $table->seeder(1, 0);
        //$rows[0]['id'] = pg_escape_string($rows[0]['id']);
        //$rows[0]['boolean'] = null;
        PdoLog::clear();
        $cntInsert = $table->insert($pdoConnection, $rows);
        $rowsSelect = $pdoConnection->sql('SELECT * FROM ' . $pdoConnection->quote($table->value(TabName::class)))->exec()->fetch();
        $rowsSelectOutput = [];
        foreach ($rowsSelect as $row) {
            $rowOutput = [];
            foreach ($row as $name => $value) {
                $rowOutput[$name] = $table->column($name)->output($value);
            }
            $rowsSelectOutput[] = $rowOutput;
        }
        echo PdoLog::getLog();
        s_dd(
            $rows,
            $cntInsert,
            $rowsSelect,
            $rowsSelectOutput
        );
    }
    File::save(__DIR__ . '/../../docs/types.html', (string)new HtmlTypes());
    //echo (string)new HtmlTypes(); exit(0);
    //s_dd($migrations, unserialize(serialize($migrations)));
    echo (string)new HtmlDiff($migrations->all());
});
