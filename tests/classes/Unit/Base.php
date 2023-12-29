<?php

namespace Shasoft\DbSchema\Tests\Unit;

use Shasoft\Pdo\PdoConnection;
use PHPUnit\Framework\TestCase;
use Shasoft\DbSchema\Command\Type;
use Shasoft\DbSchema\DbSchemaDriver;
use Shasoft\DbSchema\Command\TabName;
use Shasoft\DbSchema\Tests\Table\User;
use Shasoft\DbSchema\Column\ColumnReal;
use Shasoft\DbSchema\DbSchemaMigrations;
use Shasoft\DbSchema\Tests\Table\Article;
use Shasoft\DbSchema\Tests\Table\AllTypes;
use Shasoft\DbSchema\Tests\Table\ForSeeder;
use Shasoft\DbSchema\Tests\Table\AllMigrations;
use Shasoft\DbSchema\Tests\Table\AllIndexes;
use Shasoft\DbSchema\Tests\Table\TabExample;
use Shasoft\DbSchema\Tests\Table\TabReference1;
use Shasoft\DbSchema\Tests\Table\TabReference2;

abstract class Base extends TestCase
{
    // Имя драйвера
    protected string $driverName;
    // Параметры PDO
    protected array $pdoParams;
    // Драйвер
    protected ?DbSchemaDriver $driver;
    // Соединение БД
    protected ?PdoConnection $connection;
    //
    public function setUp(): void
    {
        parent::setUp();
        // Драйвер
        $classname = $this->driverName;
        $this->driver = new $classname;
        // Создать соединение
        $classname = $this->driver->pdoConnectionClass();
        $this->connection = new $classname($this->pdoParams);
    }
    public function tearDown(): void
    {
        parent::tearDown();
        // Уничтожить драйвер
        $this->driver = null;
        // Уничтожить соединение
        $this->connection = null;
    }
    protected function migrationTables(array $tablesClass)
    {
        // Очистить БД
        $this->connection->reset();
        // Сгенерировать миграции
        $migrations = DbSchemaMigrations::get($tablesClass, $this->driverName);
        // Выполнить миграции
        $migrations->run($this->connection);
        // Проверить что в БД все таблицы создались + 1 техническая таблица
        self::assertCount(count($tablesClass) + 1, $this->connection->tables());
        // Отмена миграций
        while ($migrations->cancel($this->connection) != 0);
        // Проверить что в БД осталась только техническая таблица
        self::assertCount(1, $this->connection->tables());
    }
    public function testMigrationAllTypes()
    {
        $this->migrationTables([AllTypes::class]);
    }
    public function testMigrationAllIndexes()
    {
        $this->migrationTables([AllIndexes::class]);
    }
    public function testMigrationAllMigrations()
    {
        $this->migrationTables([AllMigrations::class]);
    }
    public function testMigrationTabExample()
    {
        $this->migrationTables([TabExample::class]);
    }
    public function testMigrationAllForSeeder()
    {
        $this->migrationTables([ForSeeder::class]);
    }
    public function testMigrationArticleAndUser()
    {
        $this->migrationTables([Article::class, User::class]);
    }
    public function testMigrationReference()
    {
        $this->migrationTables([TabReference1::class, TabReference2::class]);
    }
    public function _testMigrationAll()
    {
        $this->migrationTables([
            User::class, Article::class,
            AllTypes::class,
            AllIndexes::class,
            ForSeeder::class,
            AllChanges::class
        ]);
    }
    public function testSeeder()
    {
        // Очистить БД
        $this->connection->reset();
        // Сгенерировать миграции
        $migrations = DbSchemaMigrations::get([ForSeeder::class], $this->driverName);
        // Выполнить миграции
        $migrations->run($this->connection);
        // Получить таблицу
        $table = $migrations->database()->table(ForSeeder::class);
        // Сгенерировать данные
        $rows = array_merge(
            $table->seeder(32, 0),
            $table->seeder(32, 0),
            $table->seeder(1, 100),
        );
        //
        $values = [];
        $id = 0;
        foreach ($rows as &$row) {
            $row['id8'] = ++$id;
            $values[$row['id8']] = $row;
        }
        // Вставить данные
        $cntInsert = $table->insert($this->connection, $rows);
        self::assertCount($cntInsert, $rows, 'Не все строки добавились');
        // Выбрать данные
        $rowsSelect = $this->connection->sql('SELECT * FROM ' . $this->connection->quote($table->value(TabName::class)))->exec()->fetch();
        // Преобразовать в формат PHP и сравнить
        foreach ($rowsSelect as $rowSelect) {
            $row = $values[$rowSelect['id8']];
            foreach ($rowSelect as $name => $value) {
                $column = $table->column($name);
                $value = $column->output($value);
                if ($column->value(Type::class) == ColumnReal::class) {
                    self::assertTrue(ColumnReal::compare($value, $row[$name]), 'Не совпадает поле ' . $name);
                } else {
                    // Сравнить значения
                    self::assertEquals($value, $row[$name], 'Не совпадает поле ' . $name);
                }
            }
        }
    }
}
