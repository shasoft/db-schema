<?php

namespace Shasoft\DbSchema;

use Shasoft\Pdo\SqlFormat;
use Shasoft\DbSchema\Command\TabName;
use Shasoft\DbSchema\State\StateDatabase;
use Shasoft\DbSchema\DbSchemaStateManager;
use Shasoft\DbSchema\DbSchemaReflection;

// Миграции
class DbSchemaMigrations
{
    // Актуальная БД
    protected StateDatabase $stateDatabase;
    // Миграции
    protected array $migrations;
    // Имя технической таблицы с миграциями
    static protected string $techTable = '@migrations';
    // Конструктор
    protected function __construct(protected string $classDriver, array $migrations)
    {
        // Актуальное состояние БД
        $this->stateDatabase = $migrations[array_key_last($migrations)]['state'];
        // Создать объект драйвера
        $driver = new $classDriver;
        // Установить поддерживаемые PDO соединения
        DbSchemaReflection::getObjectProperty($this->stateDatabase, 'pdoSupport')->setValue($this->stateDatabase, $driver->pdoSupport());
        // Добавить команды имен таблиц
        foreach ($this->stateDatabase->tables() as $classname => $table) {
            // Получить свойство команды
            $propertyCommands = DbSchemaReflection::getObjectProperty($table, 'commands');
            // Читать значение свойства
            $commands = $propertyCommands->getValue($table);
            // Добавить новую команду
            $command = new TabName($driver->tabname($classname));
            $commands[get_class($command)] = $command;
            // Установить новый список команд
            $propertyCommands->setValue($table, $commands);
        }
        //
        $this->migrations = array_map(function (array $migration) {
            $migration['name'] = $migration['state']->name();
            //unset($migration['state']);
            return $migration;
        }, $migrations);
    }
    // Имя технической таблицы с миграциями
    static public function getTechTable(): string
    {
        return self::$techTable;
    }
    public function __serialize(): array
    {
        // Удалим состояние БД для каждой миграции
        return [
            $this->stateDatabase,
            $this->classDriver,
            array_map(function (array $migration) {
                unset($migration['state']);
                return $migration;
            }, $this->migrations)
        ];
    }
    public function __unserialize(array $data): void
    {
        $this->stateDatabase = $data[0];
        $this->classDriver = $data[1];
        $this->migrations = $data[2];
    }
    // Получить миграции
    static public function get(array $tableClasses, string $classDriver): static
    {
        $stateDatabase = DbSchemaStateManager::get($tableClasses);
        // Создать драйвер
        $driver = new $classDriver;
        //
        $migrations = [];
        //
        $stateDatabases = [];
        $tabNames = [];
        $state = $stateDatabase;
        while ($state) {
            $stateDatabases[] = $state;
            $tabNames = array_merge($tabNames, array_keys($state->tables()));
            $state = $state->parent();
        }
        $tabNames = array_unique($tabNames);
        $stateDatabases[] = null;
        $stateDatabases = array_reverse($stateDatabases);
        $refMethodDiffInt = DbSchemaReflection::getObjectMethod($driver, 'diffInt');
        foreach ($tabNames as $tabname) {
            for ($i = 1; $i < count($stateDatabases); $i++) {
                $up = $refMethodDiffInt->invoke($driver, $stateDatabases[$i - 1], $stateDatabases[$i], $tabname);
                $down = $refMethodDiffInt->invoke($driver, $stateDatabases[$i], $stateDatabases[$i - 1], $tabname);
                //
                if (!empty($up) || !empty($down)) {
                    $name = $stateDatabases[$i]->name();
                    //
                    if (!array_key_exists($name, $migrations)) {
                        // Клонировать
                        $state = unserialize(serialize($stateDatabases[$i]));
                        //s_dd($state, $stateDatabases[$i]);
                        // Обнулить родительскую БД
                        DbSchemaReflection::getObjectProperty($state, 'parent')->setValue($state, null);
                        // Удалить из таблиц все удаленные объекты
                        foreach ($state->tables() as $table) {
                            DbSchemaReflection::getObjectProperty($table, 'drops')->setValue($table, []);
                        }
                        //
                        $migrations[$name] = [
                            'state' => $state,
                            'migrations' => []
                        ];
                    }
                    if (!array_key_exists($tabname, $migrations[$name]['migrations'])) {
                        $migrations[$name]['migrations'][$tabname] = [
                            'up' => [],
                            'down' => []
                        ];
                    }
                    $migrations[$name]['migrations'][$tabname]['up'] = array_merge($migrations[$name]['migrations'][$tabname]['up'], $up);
                    $migrations[$name]['migrations'][$tabname]['down'] = array_merge($migrations[$name]['migrations'][$tabname]['down'], $down);
                }
            }
        }
        $migrations = array_values($migrations);
        // Сортировать по миграциям
        usort($migrations, function (array $item1, array $item2) {
            return strcmp($item1['state']->name(), $item2['state']->name());
        });
        // Вернуть объект
        return new static($classDriver, $migrations);
    }
    // Перебрать все миграции
    public function each(\Closure $cb): void
    {
        foreach ($this->migrations as $migrationItem) {
            foreach ($migrationItem['migrations'] as $tabname => $migration) {
                $cb($migrationItem['name'], $tabname, $migration['up'], $migration['down']);
            }
        }
    }
    // Получить все миграции
    public function all(): array
    {
        return $this->migrations;
    }
    // Получить актуальное состояние БД
    public function database(): StateDatabase
    {
        return $this->stateDatabase;
    }
    // Выполнить миграции
    public function run(\PDO $pdo): void
    {
        // Создать драйвер миграции
        $classname = $this->classDriver;
        $driver = new $classname();
        // Получить состояние для технической таблицы
        $stateDatabase = DbSchemaStateManager::get([DbSchemaTechTable::class]);
        // Проверим наличие таблицы БД
        // Для этого просто выберем из неё данные
        try {
            $sql = 'SELECT * FROM ' . $driver->quote(self::getTechTable());
            $pdo->query($sql);
        } catch (\PDOException $e) {
            // Если это ошибка отсутствия таблицы
            if ($e->getCode() == '42S02' || $e->getCode() == '42P01') {
                // Создать техническую таблицу
                // Выполнить все миграции создания технической таблицы
                $techMigrations = $stateDatabase->migrations($driver);
                foreach ($techMigrations as $migrationItem) {
                    foreach ($migrationItem['migrations'] as $tabname => $item) {
                        foreach ($item['up'] as $sql) {
                            // Заменить имя таблицы на имя технической таблицы миграций
                            $sql = str_replace($driver->tabname(DbSchemaTechTable::class), self::$techTable, $sql);
                            // Выполнить SQL
                            $pdo->query($sql);
                        }
                    }
                }
            } else {
                // Иначе прокинем дальше исключение
                throw $e;
            }
        }
        // Определить номер
        $okDbSchemaMigrations = [];
        $num = 1;
        $rows = $pdo->query('SELECT * FROM ' . $driver->quote(self::$techTable))->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $okDbSchemaMigrations[$row['name'] . "\n" . $row['classname']] = 1;
            //
            $n = intval($row['num']);
            if ($n >= $num) {
                $num = $n + 1;
            }
        }
        // Выполнить все миграции которые не выполнялись
        // INSERT INTO <имя таблицы>[(<имя столбца>,...)] {VALUES (<значение столбца>,…)}
        $queryInsert = $pdo->prepare(
            'INSERT INTO ' .
                $driver->quote(self::$techTable) .
                ' (' .
                $driver->quote('num') . ',' .
                $driver->quote('sub') . ',' .
                $driver->quote('name') . ',' .
                $driver->quote('classname') . ',' .
                $driver->quote('up') . ',' .
                $driver->quote('down') .
                ') VALUES (:num, :sub, :name, :classname, :up, :down)'
        );
        $sub = 0;
        foreach ($this->migrations as $migrationItem) {
            foreach ($migrationItem['migrations'] as $classname => $item) {
                // Ключ 
                $key = $migrationItem['name'] . "\n" . $classname;
                // Если миграция не выполнялась
                if (!array_key_exists($key, $okDbSchemaMigrations)) {
                    // то выполнить её
                    foreach ($item['up'] as $sql) {
                        // Выполнить SQL
                        $pdo->query($sql);
                    }
                    $sub++;
                    //-- Сохранить выполненную миграцию 
                    // Привязать значения
                    $queryInsert->bindValue('num', $num, \PDO::PARAM_INT);
                    $queryInsert->bindValue('sub', $sub, \PDO::PARAM_INT);
                    $queryInsert->bindValue('name', $migrationItem['name'], \PDO::PARAM_STR);
                    $queryInsert->bindValue('classname', $classname, \PDO::PARAM_STR);
                    $queryInsert->bindValue('up', json_encode($item['up']), \PDO::PARAM_STR);
                    $queryInsert->bindValue('down', json_encode($item['down']), \PDO::PARAM_STR);
                    // Выполнить
                    $queryInsert->execute();
                }
            }
        }
    }
    // Отменить последнюю миграцию
    public function cancel(\PDO $pdo): int
    {
        $ret = 0;
        // Создать драйвер миграции
        $classname = $this->classDriver;
        $driver = new $classname();
        // Читать данные из технической таблицы
        try {
            $rows = $pdo->query('SELECT * FROM ' . $driver->quote(self::$techTable))->fetchAll(\PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                $num = $rows[0]['num'];
                foreach ($rows as $row) {
                    //
                    $n = intval($row['num']);
                    if ($n >= $num) {
                        $num = $n;
                    }
                }
                $ret = $num;
                // Выбрать миграции с нужным номером
                $rows = array_filter($rows, function (array $row) use ($num) {
                    return $row['num'] == $num;
                });
                // Отсортировать в обратном порядке
                usort($rows, function (array $row1, array $row2) {
                    return $row1['sub'] < $row2['sub'] ? 1 : ($row1['sub'] > $row2['sub'] ? -1 : 0);
                });
                // INSERT INTO <имя таблицы>[(<имя столбца>,...)] {VALUES (<значение столбца>,…)}
                $sql =
                    'DELETE FROM ' .
                    $driver->quote(self::$techTable) .
                    ' WHERE ' .
                    $driver->quote('num') . ' = :num' .
                    ' AND ' .
                    $driver->quote('sub') . ' = :sub';
                $queryDelete = $pdo->prepare($sql);
                // Выполнить все миграции отмены
                foreach ($rows as $row) {
                    $down = json_decode($row['down'], true);
                    foreach ($down as $sql) {
                        $pdo->query($sql);
                    }
                    // Привязать значения
                    $queryDelete->bindValue('num', $row['num'], \PDO::PARAM_INT);
                    $queryDelete->bindValue('sub', $row['sub'], \PDO::PARAM_INT);
                    // Удалить миграцию из таблицы
                    $queryDelete->execute();
                }
            }
        } catch (\Exception $e) {
        }
        return $ret;
    }
    /*
    // Вывод для отладки
    public function dump(): void
    {
        echo '<div style="padding:4px;border:solid 1px green">';
        //
        $rows = debug_backtrace();
        echo '<div>file: <strong style="color:green">' . $rows[0]['file'] . ':' . $rows[0]['line'] . '</strong></div>';
        // Вывести
        foreach ($this->migrations as $migrationItem) {
            echo '<div style="padding:4px;border:solid 1px Teal;background-color:LightCyan">';
            echo '<div style="padding:2px;background-color:PaleTurquoise"><strong style="color:DarkCyan">' . $migrationItem['name'] . '</strong></div>';
            foreach ($migrationItem['migrations'] as $tabname => $migration) {
                foreach ($migration['up'] as $sql) {
                    echo SqlFormat::auto($sql);
                }
            }
            echo '</div>';
        }
        echo '</div>';
    }
    //*/
};
