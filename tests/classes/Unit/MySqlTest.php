<?php

namespace Shasoft\DbSchema\Tests\Unit;

use Shasoft\DbSchema\Driver\DbSchemaDriverMySql;

class MySqlTest extends Base
{
    // Имя драйвера
    protected string $driverName = DbSchemaDriverMySql::class;
    // Параметры PDO
    protected string $pdoDsn = 'mysql:dbname=cmg-db-test;host=localhost';
    protected ?string $username = 'root';
}
