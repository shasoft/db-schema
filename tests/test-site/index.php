<?php

use Shasoft\Pdo\PdoLog;
use Shasoft\Pdo\SqlFormat;
use Shasoft\Filesystem\File;
use Shasoft\DbTool\DbToolPdo;
use Shasoft\DbSchemaDev\HtmlDiff;
use Shasoft\DbSchemaDev\HtmlTypes;
use Shasoft\DbSchemaDev\Table\User;
use Shasoft\DbSchema\Command\TabName;
use Shasoft\DbSchemaDev\Table\Article;
use Shasoft\DbSchemaDev\Table\TabTest;
use Shasoft\DbSchema\Column\ColumnReal;
use Shasoft\DbSchemaDev\Table\AllTypes;
use Shasoft\DbSchema\DbSchemaMigrations;
use Shasoft\DbSchemaDev\DbSchemaDevTool;
use Shasoft\DbSchemaDev\Table\ForSeeder;
use Shasoft\DbSchemaDev\Table\AllChanges;
use Shasoft\DbSchemaDev\Table\AllIndexes;
use Shasoft\DbSchemaDev\Table\TabExample;
use Shasoft\DbSchema\DbSchemaStateManager;
use Shasoft\DbSchemaDev\Table\TabExample1;
use Shasoft\DbSchemaDev\Table\TabExample2;
use Shasoft\DbSchemaDev\Table\TabExample3;
use Shasoft\DbSchemaDev\Table\TabExample4;
use Shasoft\DbSchemaDev\Table\TabExample5;
use Shasoft\DbSchemaDev\Table\TabReference;
use Shasoft\DbSchemaDev\Table\AllMigrations;
use Shasoft\DbSchemaDev\Table\TabReference1;
use Shasoft\DbSchemaDev\Table\TabReference2;
use Shasoft\Pdo\Connection\PdoConnectionMySql;
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
    // Создать PDO соединение
    $pdo = new \PDO('mysql:dbname=cmg-db-test;host=localhost', 'root');
    // Драйвер
    $driver = new DbSchemaDriverMySql;
    /*
    $pdoConnection = new PdoConnectionMySql([
        'dbname' => 'cmg-db-test',
        'host' => 'localhost',
        'username' => 'root'
    ]);
    //$pdo = new \PDO('pgsql:dbname=cmg-db-test;host=localhost', 'postgres', '123');
    $pdoStatement =  $pdo->prepare('SELECT * FROM `@migrations`');
    $pdoStatement->execute();
    $rows = $pdoStatement->fetch(\PDO::FETCH_ASSOC);
    s_dd($pdoStatement, $rows);
    s_dd(
        $rows,
        $pdoConnection,
        $pdo,
        $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME),
        $pdo->quote('tabName')
    );
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
    DbToolPdo::reset($pdo);
    // Получить миграции
    //xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY + XHPROF_FLAGS_NO_BUILTINS);
    $migrations = DbSchemaMigrations::get(
        [
            //Article::class, User::class,
            //TabTest::class,
            //AllTypes::class,
            //AllMigrations::class,
            //AllIndexes::class,
            ForSeeder::class,
            //TabExample5::class,
            //TabReference1::class,
            //TabReference2::class
        ],
        get_class($driver)
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
    $migrations->run($pdo);

    // Отменить последнюю миграцию
    /*
    $rc = 1;
    while ($rc != 0) {
        $rc = $migrations->cancel($pdo);
        s_dump($rc);
    }
    //*/
    s_dump($migrations, $migrations->database());

    // Сгенерировать данные
    if ($migrations->database()->hasTable(ForSeeder::class)) {
        $table = $migrations->database()->table(ForSeeder::class);
        // Сгенерировать данные
        $rows = $table->seeder(1, 0);
        // Вставить
        $cntInsert = DbSchemaDevTool::insert($pdo, $table, $rows);
        // Выбрать
        $rowsSelect = DbToolPdo::query($pdo, 'SELECT * FROM ' . $driver->quote($table->value(TabName::class)));
        $rowsSelectOutput = [];
        foreach ($rowsSelect as $row) {
            $rowOutput = [];
            foreach ($row as $name => $value) {
                $rowOutput[$name] = $table->column($name)->output($value);
            }
            $rowsSelectOutput[] = $rowOutput;
        }
        //echo PdoLog::getLog();
        s_dump(
            $rows,
            $cntInsert,
            $rowsSelect,
            $rowsSelectOutput
        );
    }
    /*
    $htmlTypes = new HtmlTypes([__DIR__ . '/../../classes']);
    $htmlTypes->save(__DIR__ . '/../../docs/~/db-schema.html');
    echo (string)$htmlTypes;
    exit(0);
    //*/
    /*
    $htmlMigrations = new HtmlDiff($migrations->all());
    $htmlMigrations->save(__DIR__ . '/../../docs/~/db-schema-all-migrations.html');
    echo (string)$htmlMigrations;
    //*/
});
