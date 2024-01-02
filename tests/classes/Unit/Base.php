<?php

namespace Shasoft\DbSchema\Tests\Unit;

use Shasoft\DbTool\DbToolPdo;
use PHPUnit\Framework\TestCase;
use Shasoft\DbSchema\Command\Type;
use Shasoft\DbSchema\DbSchemaDriver;
use Shasoft\DbSchemaDev\Table\User;
use Shasoft\DbSchema\Column\ColumnReal;
use Shasoft\DbSchema\DbSchemaMigrations;
use Shasoft\DbSchemaDev\DbSchemaDevTool;
use Shasoft\DbSchemaDev\Table\Article;
use Shasoft\DbSchemaDev\Table\AllTypes;
use Shasoft\DbSchemaDev\Table\ForSeeder;
use Shasoft\DbSchemaDev\Table\AllIndexes;
use Shasoft\DbSchemaDev\Table\TabExample;
use Shasoft\DbSchemaDev\Table\AllMigrations;
use Shasoft\DbSchemaDev\Table\TabReference1;
use Shasoft\DbSchemaDev\Table\TabReference2;

abstract class Base extends TestCase
{
    // Имя драйвера
    protected string $driverName;
    // Параметры PDO
    protected string $pdoDsn;
    protected ?string $username = null;
    protected ?string $password = null;
    // PDO
    protected ?\PDO $pdo;
    // Драйвер
    protected ?DbSchemaDriver $driver;
    //
    public function setUp(): void
    {
        parent::setUp();
        // Драйвер
        $classname = $this->driverName;
        $this->driver = new $classname;
        // Создать PDO соединение
        $this->pdo = new \PDO($this->pdoDsn, $this->username, $this->password);
    }
    public function tearDown(): void
    {
        parent::tearDown();
        // Уничтожить драйвер
        $this->driver = null;
        // Уничтожить соединение
        $this->pdo = null;
    }
    protected function migrationTables(array $tablesClass)
    {
        // Очистить БД
        DbToolPdo::reset($this->pdo);
        // Сгенерировать миграции
        $migrations = DbSchemaMigrations::get($tablesClass, $this->driverName);
        // Выполнить миграции
        $migrations->run($this->pdo);
        // Проверить что в БД все таблицы создались + 1 техническая таблица
        self::assertCount(count($tablesClass) + 1, DbToolPdo::tables($this->pdo));
        // Отмена миграций
        while ($migrations->cancel($this->pdo) != 0);
        // Проверить что в БД осталась только техническая таблица
        self::assertCount(1, DbToolPdo::tables($this->pdo));
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
        DbToolPdo::reset($this->pdo);
        // Сгенерировать миграции
        $migrations = DbSchemaMigrations::get([ForSeeder::class], $this->driverName);
        // Выполнить миграции
        $migrations->run($this->pdo);
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
        $cntInsert = DbSchemaDevTool::insert($this->pdo, $table, $rows);
        self::assertCount($cntInsert, $rows, 'Не все строки добавились');
        // Выбрать данные
        $sql = 'SELECT * FROM ' . DbToolPdo::quote($this->pdo, $table->tabname());
        $rowsSelect = DbToolPdo::query($this->pdo, $sql);
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
