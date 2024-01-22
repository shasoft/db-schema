CHANGE LOG
==========

## v1.0 (2023-12-29)
* Первая версия

## v1.0.1 (2023-12-29) 
* Выделил документирование в отдельный пакет [shasoft/db-schema-doc](https://github.com/shasoft/db-schema-doc)

## v1.0.2 (2023-12-29)
* Добавил в зависимости пакет [shasoft/reflection](https://github.com/shasoft/reflection)

## v1.0.3
* Добавил команду для указания источника определения отношения Origin
* Доработал генератор для ColumnString

## v2.0 (2024-01-02)
* Убрал все зависимости. 
* Переписал для работы с [PDO](https://www.php.net/manual/ru/book.pdo.php)

## v2.0.1 (2024-01-22)
* Удалил функцию StateTable::seeder (перенес её в пакет [shasoft/db-schema-dev](https://github.com/shasoft/db-schema-dev))
* Переименовал команду Comment в Title
* Добавил команду Custom для создания пользовательских команд
* Доработка функции Column->input/output
* Добавил функцию для расчета расширенных данных миграций DbSchemaMigrations->extraData