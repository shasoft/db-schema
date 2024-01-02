## Версионная миграция структуры базы данных через PHP атрибуты

Простой пример определения таблицы БД:
```php
#[Comment('Таблица для примера')]
class TabExample
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
В данном примере через класс TabExample определяется таблица, содержащая два поля и один индекс. Миграции для указанного примера создаются следующим образом:
```php
    // Получить миграции
    $migrations = DbSchemaMigrations::get([
            // Класс таблицы
            TabExample::class,      
        ],
        // Драйвер для получения миграций
        DbSchemaDriverMySql::class   
    );
    // Создать PDO соединение
    $pdo = new \PDO(
        'mysql:dbname=cmg-db-test;host=localhost', 
        'root'
    );
    // Выполнить миграции
    $migrations->run($pdo);
```
В результате выполнения миграций в БД создастся таблица БД с помощью следующего SQL кода
```sql
CREATE TABLE `tabexample`(
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор',
    `name` VARCHAR(255) NULL COMMENT 'Имя',
    PRIMARY KEY(`id`) USING BTREE
) COMMENT 'Таблица для примера';
```
Можно отменить последнии миграции с помощью следующего кода
```php
// Отменить последнии миграции
$migrations->cancel($pdo);
```
эти миграции будут отменены с помощью следующего SQL кода
```sql
DROP TABLE IF EXISTS
    `tabexample`
```

[Подробнее](docs/index.md)
