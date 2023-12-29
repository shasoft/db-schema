## Версионная миграция структуры базы данных через PHP атрибуты

Всегда немного раздражало что при написании миграций в Laravel сначала необходимо прописывать поля в классе модели, а затем эти же поля в миграциях. И когда мне понадобилось написать версионирование структуры БД, то решил совместить класс модели и миграции.  И сделал я это через [атрибуты PHP](https://www.php.net/manual/ru/language.attributes.overview.php). Также вместе с миграциями я получил состояние базы данных которое можно использовать при работе с ней.

### Введение

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
    // Создать PDO соединение
    $pdoConnection = new PdoConnectionMySql([
        'dbname' => 'cmg-db-test',
        'host' => 'localhost',
        'username' => 'root'
    ]);
    // Получить миграции
    $migrations = DbSchemaMigrations::get([
            TabExample::class,      // Класс таблицы
        ],
        DbSchemaDriverMySql::class   // Драйвер для получения миграций
    );
    // Выполнить миграции
    $migrations->run($pdoConnection);
```
В результате выполнения миграций в БД создастся таблица БД с помощью следующего SQL кода
```sql
CREATE TABLE `shasoft-dbschema-tests-table-tabexample`(
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
    `shasoft-dbschema-tests-table-tabexample`
```

Основная идея очень простая: создается класс таблицы, в котором определяются колонки(поля), [индексы](https://habr.com/ru/companies/ruvds/articles/724066/), [отношения](https://habr.com/ru/articles/193380/) и ссылки на поля.
Каждая сущность (таблица, колонка, индекс, отношение, ссылка на поле) поддерживает заданный список команд, которые можно указывать через атрибуты PHP. В примере выше используются команды `Comment` и `Columns`.
Если нам нужно добавить в класс новую миграцию, то сделать это можно с помощью команды `Migration` следующим образом:
```php
#[Comment('Таблица для примера')]
class TabExample
{
    #[Comment('Идентификатор')]
    protected ColumnId $id;
    #[Comment('Имя')]
    protected ColumnString $name;
    #[Migration('2023-12-28T22:00:00+03:00')]
    #[Comment('Фамилия')]
    protected ColumnString $fam;
    // Первичный ключ
    #[Columns('id')]
    protected IndexPrimary $pkKey;
}
```
Т.е. указываем команду `Migration` в качестве параметров строку с датой/временем миграции (можно указывать не строку, а объект DateTime) и после указываем команды изменений которые вносит эта миграция. В данном случае мы добавили новое поле **fam**. В результате миграции будут содержать две SQL команды. Первая команда - создание таблицы (как в первом примере) и вторая команда - добавление нового поля:
```sql
ALTER TABLE
    `shasoft-dbschema-tests-table-tabexample` ADD `fam` VARCHAR(255) NULL COMMENT 'Фамилия'
```
Добавим ещё одну миграцию с переименованием поля и удалением поля
```php
#[Comment('Таблица для примера')]
class TabExample
{
    #[Comment('Идентификатор')]
    protected ColumnId $id;
    #[Comment('Имя')]
    #[Migration('2023-12-28T22:10:00+03:00')]
    #[Drop]
    protected ColumnString $name;
    #[Migration('2023-12-28T22:00:00+03:00')]
    #[Comment('Фамилия')]
    #[Migration('2023-12-28T22:10:00+03:00')]
    #[Name('surname')]
    protected ColumnString $fam;
    // Первичный ключ
    #[Columns('id')]
    protected IndexPrimary $pkKey;
}
```
И тогда в миграции добавится ещё две SQL команды для удаления
```sql
ALTER TABLE
    `shasoft-dbschema-tests-table-tabexample`
DROP COLUMN
    `name`;
```
и переименования поля
```sql
ALTER TABLE
    `shasoft-dbschema-tests-table-tabexample` CHANGE `fam` `surname` VARCHAR(255) NULL COMMENT 'Фамилия';
```

### Типы колонок (полей)

На текущий момент поддерживаются основные типы БД
- **ColumnString** - Текст
- **ColumnInteger** - Целое число
- **ColumnReal** - Вещественное число
- **ColumnBoolean** - Логическое значение
- **ColumnBinary** - Двоичные данные
- **ColumnDatetime** - Дата/время
- **ColumnDecimal** - Число с фиксированной точностью
- **ColumnEnum** - Перечисление

И дополнительные типы (они основаны на основных)
- **ColumnId** - Идентификатор
- **ColumnJson** - Json данные

Для примера рассмотрим тип **ColumnInteger** - Целое число. Поле содержащие целое число может быть 8, 16, 24, 32, 48 и 64 битным в зависимости от БД. При этом какие БД поддерживают 48 битные целые поля, какие-то нет. Именно поэтому нет команд, которые определяют размерность числа в битах, зато есть команды `MinValue` И `MaxValue` которые определяют минимальное и максимальное значение поля. А уже на основе этих значений драйвер БД определяет какой тип поля необходим для хранения. По умолчанию `MinValue` = PHP_INT_MIN, `MaxValue` = PHP_INT_MAX. Однако эти значения можно переопределить с помощью команд при определении поля.
```php
#[Comment('Таблица для примера')]
class TabExample
{
    #[Comment('Рост человека, мм')]
    #[MinValue(0)]
    #[MaxValue(4000)]
    protected ColumnInteger $rost;
}
```
SQL код для [MySql](https://metanit.com/sql/mysql/2.3.php)
```sql
CREATE TABLE `shasoft-dbschema-tests-table-tabexample`(
    `rost` SMALLINT NULL COMMENT 'Рост человека, мм'
) COMMENT 'Таблица для примера';
```
По умолчанию значение колонки(поля) может быть NULL. Однако можно переопределить значение по умолчанию с помощью команды `DefaultValue`
```php
#[Comment('Таблица для примера')]
class TabExample
{
    #[Comment('Рост человека, мм')]
    #[MinValue(0)]
    #[MaxValue(4009)]
    #[DefaultValue(1800)]
    protected ColumnInteger $rost;
}
```
и получаем код где по умолчанию рост устанавливается = 1800
```sql
CREATE TABLE `shasoft-dbschema-tests-table-tabexample`(
    `rost` SMALLINT DEFAULT 1800 COMMENT 'Рост человека, мм'
) COMMENT 'Таблица для примера';
```

### Состояние базы данных и её использование

Выше упоминалось что можно не просто выполнить миграции, но и получить актуальное состояние БД. Состояние содержит все сущности, входящие в БД, и их команды. К примеру следующим образом можно получить максимальное значение колонки **id**:
```php
// Получить максимальное значение колонки id
$migrations
    ->database()
    ->table(TabExample::class)
    ->column('id')
    ->value(MaxValue::class);
```
аналогичным образом можно получить значение любой команды любой сущности входящей в БД.

Зная минимальное и максимальное значение колонки мы можем легко сгенерировать случайное значение колонки(поля). Также в список поддерживаемых команд входит команда `Seeder` в которой можно задать статический метод класса/функцию для генерации случайного значения. А чтобы процесс генерации данных сделать совсем простым в состоянии таблицы добавлен метод seeder, который генерирует строку данных для таблицы:
```php
// Сгенерировать 10 строк случайных значений
$rows = $migrations
    ->database()
    ->table(TabExample::class)
    ->seeder(10,30);
```
Код выше генерирует 10 строк со случайными данными для указанной таблицы. Количество строк задаётся первым параметром. Вторым параметром задаётся вероятность установки поля в значение NULL (если колонка такое поддерживает).

Сгенерированные строки необходимо добавить в таблицу БД. И тут возникает необходимость конвертировать значения из формата PHP в формат БД и обратно. И для этого тоже есть свои команды:
- `ConversionInput` - конвертировать из формата PHP в формат БД
- `ConversionOutput` - конвертировать из формата БД в формат PHP

В качестве параметра указывается статический метод класса/функция для конвертации значений. Ниже представлен тип колонки Json данных в котором показано использование команд конвертации:
```php
// Json данные
class ColumnJson extends ColumnString
{
    // Конструктор
    public function __construct()
    {
        // Вызвать конструктор родителя
        parent::__construct();
        // Удалить команды
        $this->removeCommand(Seeder::class);
        // Установить команды
        $this->setCommand(new Comment('Json данные'));
        $this->setCommand(new MaxLength(256 * 256 - 1));
        $this->setCommand(new DefaultValue());
        $this->setCommand(new ConversionInput(self::class . '::inputJson'), false);
        $this->setCommand(new ConversionOutput(self::class . '::outputJson'), false);
        // Удалить команды из списка поддерживаемых
        $this->removeSupportCommand(Variable::class);
    }
    // PHP=>БД
    public static function inputJson(array|null $value): ?string
    {
        if (is_null($value)) {
            return null;
        }
        return is_array($value) ? (json_encode($value, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE)) : '{}';
    }
    // БД=>PHP
    public static function outputJson(string|null $value): ?array
    {
        if (is_null($value)) {
            return null;
        }
        $ret = [];
        if (!empty($value) && is_string($value)) {
            $ret = json_decode($value, true);
        }
        return $ret;
    }
};
```

Теперь чтобы произвести конвертацию данных достаточно из состояния колонки  получить соответствующую команду и вызвать нужный метод. Для добавления данных в состоянии таблицы уже реализован метод **insert** который вызывает методы конвертации:
```php
// Вставить в таблицу БД сгенерированные ранее строки
$rows = $migrations
    ->database()
    ->table(TabExample::class)
    ->insert($pdoConnection, $rows);
```
В качестве параметра метод получает объект PDO соединения с БД и строки таблицы.

# Индексы

Для ускорения работы с БД используются индексы. Поддерживаются следующие типы индексов:

- **IndexPrimary** - Первичный ключ (индекс)
- **IndexUnique** - Уникальный индекс
- **IndexKey** - Неуникальный индекс

Индексы поддерживают обязательную команду `Columns`(т.е. без её указания будет генерироваться ошибка)  которая задаёт список полей индекса.

# Ссылки на поля

Иногда необходимо в одной таблице ссылаться на поле в другой таблице. К примеру в таблице Статьи добавить поле идентификатор пользователя который ссылается на поле в таблице Пользователи. При этом необходимо чтобы при изменении типа колонки в таблице Пользователи во всех таблицах где идет ссылка на это поле тоже бы изменялся тип. Для этого и существует сущность - **Reference**. Пример использования будет показан в разделе **Отношения**.

# Отношения

Обычно таблицы связываются отношениями. Поддерживаются следующие виды отношений:

- **RelationManyToOne** - Отношение многие-к-одному
- **RelationOneToMany** - Отношение один-ко-многим
- **RelationOneToOne** - Отношение один-к-одному

В коде ниже демонстрируется пример отношения Многие-к-Одному. Нескольким статьям может соответствовать один пользователь.
```php
#[Comment('Пользователи')]
class User
{
    //
    #[Comment('Идентификатор')]
    protected ColumnId $id;
    #[Comment('Имя')]
    protected ColumnString $name;
    #[Columns('id')]
    protected IndexPrimary $pkId;
}
#[Comment('Статьи')]
class Article
{
    //
    #[Comment('Идентификатор')]
    protected ColumnId $id;
    #[Comment('Ссылка на автора')]
    #[ReferenceTo(User::class, 'id')]
    protected Reference $userId;
    #[Comment('Название')]
    protected ColumnString $title;
    #[Columns('id')]
    protected IndexPrimary $pkId;
    // Отношение
    #[RelTableTo(User::class)]
    #[RelNameTo('articles')]
    #[Columns(['userId' => 'id'])]
    #[Comment('Автор')]
    protected RelationManyToOne $author;
}
```
В таблице **Article** определяется поле userId вида **Reference** (Ссылка на поле) и с помощью команды `ReferenceTo` указывается ссылочное поле. Также указывается отношение **author** со всеми нужными параметрами. В результате в таблице **Article** и **User** будут созданы все необходимые индексы для быстрого поиска по этим отношениям. Т.е. в таблице Article нет необходимости создавать индекс по полю userId, он будет создан на основе указанного отношения. Также через состояние БД можно получить всю информацию об этом отношении.
В принципе можно создавать внешние связи в тех БД, где это поддерживается. Но пока отношение - это просто создание соответствующих индексов + информация о них в состоянии БД.

[Справка по всем сущностям](types.html) (таблица, колонка(поле), индекс, отношение, ссылка на поле)