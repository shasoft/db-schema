## Версионная миграция структуры базы данных через PHP атрибуты

Простой пример определения таблицы БД:
```php
#[Comment('Таблица для примера')]
class TabExample1
{
    #[Comment('Идентификатор')]
    protected ColumnId $id;
    #[Comment('Имя')]
    protected ColumnString $name;
    // Первичный ключ
    #[Columns('id')]
    protected IndexPrimary $pkKey;
}
```
В данном примере через класс TabExample1 определяется таблица, содержащая два поля и один индекс. Миграции для указанного примера создаются следующим образом:
```php
    // Создать PDO соединение
    $pdoConnection = new PdoConnectionMySql([
        'dbname' => 'cmg-db-test',
        'host' => 'localhost',
        'username' => 'root'
    ]);
    // Получить миграции
    $migrations = DbSchemaMigrations::get([
            TabExample1::class,      // Класс таблицы
        ],
        DbSchemaDriverMySql::class   // Драйвер для получения миграций
    );
    // Выполнить миграции
    $migrations->run($pdoConnection);
```
В результате выполнения миграций в БД создастся таблица БД с помощью следующего SQL кода
```sql
CREATE TABLE `shasoft-dbschema-tests-table-tabexample1`(
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор',
    `name` VARCHAR(255) NULL COMMENT 'Имя',
    PRIMARY KEY(`id`) USING BTREE
) COMMENT 'Таблица для примера';
```
Можно отменить последнии миграции с помощью следующего кода
```php
// Отменить последнии миграции
$migrations->cancel($pdoConnection);
```
эти миграции будут отменены с помощью следующего SQL кода
```sql
DROP TABLE IF EXISTS
    `shasoft-dbschema-tests-table-tabexample1`
```

[Подробнее](docs/index.md)
