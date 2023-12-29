<?php

namespace Shasoft\DbSchema\Tests\Unit;

use Shasoft\Pdo\Connection\PdoConnectionPostgreSql;
use Shasoft\DbSchema\Driver\DbSchemaDriverPostgreSql;

class PostgreSqlTest extends Base
{
    // Имя драйвера
    protected string $driverName = DbSchemaDriverPostgreSql::class;
    // Параметры PDO
    protected array $pdoParams = [
        'dbname' => 'cmg-db-test',
        'host' => 'localhost',
        'username' => 'postgres',
        'password' => '123'
    ];
}
