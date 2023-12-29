<?php

namespace Shasoft\DbSchema\Tests\Unit;

use Shasoft\DbSchema\Driver\DbSchemaDriverMySql;

class MySqlTest extends Base
{
    // Имя драйвера
    protected string $driverName = DbSchemaDriverMySql::class;
    // Параметры PDO
    protected array $pdoParams = [
        'dbname' => 'cmg-db-test',
        'host' => 'localhost',
        'username' => 'root'
    ];
}
