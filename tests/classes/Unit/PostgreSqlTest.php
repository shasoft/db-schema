<?php

namespace Shasoft\DbSchema\Tests\Unit;

use Shasoft\DbSchema\Driver\DbSchemaDriverPostgreSql;

class PostgreSqlTest extends Base
{
    // Имя драйвера
    protected string $driverName = DbSchemaDriverPostgreSql::class;
    // Параметры PDO
    protected string $pdoDsn = 'pgsql:dbname=cmg-db-test;host=localhost';
    protected ?string $username = 'postgres';
    protected ?string $password = '123';
}
