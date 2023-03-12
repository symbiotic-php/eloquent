# Symbiotic (Laravel Eloquent)

**Пакет-обертка Eloquent (`laravel/database`) для параллельной независимой работы c Laravel отдельных приложений.**

## Установка

```
composer require symbiotic/eloquent
```

## Описание

При совместной работе стороннего функционала, использующего пакет laravel/database возникают конфликты конфигурации
подключений.
Текущий пакет наследует класс модели и менеджера, что позволяет работать параллельно с Laravel нескольким
независимым библиотекам со своими настройками соединений и использовать модели Laravel.
Также имеется возможность настраивать подключения по базовым неймспейсам, 
это дает возможность для каждого пакета задать свое соединение.

### Использование

Инициализация менеджера

```php

  $config = [
       'default' => 'my_connect_name',
        // Подключения по пакетам
        'namespaces' => [
           '\\Modules\\Articles' => 'mysql_dev',
        ]
       'connections' => [
            'my_connect_name' => [
                'driver' => 'mysql',
                'database' => 'database',
                'username' => 'root',
                'password' => 'toor',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
            ],
            'mysql_dev' => [
             // ....
            ],
        ]
    ];
    
  // Постройка из массива  {@link https://github.com/symbiotic-php/database}
  $connectionsConfig = \Symbiotic\Database\DatabaseManager::fromArray($config);
  
  // Инициализация менеджера
  $manager = new \Symbiotic\Database\Eloquent\EloquentManager(
        $connectionsConfig,
        \Symbiotic\Database\Eloquent\SymbioticModel::class // Базовый класс моделей 
        // Если вы используете модели Laravel -  \Illuminate\Database\Eloquent\Model::class
        );
            
  // Дополнительно можете установить Диспетчер событий (\Illuminate\Contracts\Events\Dispatcher)
  $manager->setEventDispatcher(new Dispatcher());

  // Установка вашего менеджера подключений для моделей
  $manager->bootEloquent();
  
  // Ваш код и запросы....
  
  // Извлечение текущего менеджера соединений и установка предыдущего (если есть)
  $manager->popEloquent();

```

При инициализации базовой модели Laravel `\Illuminate\Database\Eloquent\Model::class` вы глобально перезапишете менеджер
подключений во всех наследниках модели,
поэтому обязательно извлекайте ваш менеджер подключений после окончания работы вашего функционала.
Вы можете использовать наследованную модель `\Symbiotic\Database\Eloquent\SymbioticModel::class` как базовую,
в ней исключены все конфликты конфигураций.

Рекомендуется наследоваться от класса SymbioticModel:

```php

use Symbiotic\Database\Eloquent\SymbioticModel;
 
class User extends SymbioticModel
{
    //....
}

```

### Основные методы менеджера

```php
/**
* @var  \Symbiotic\Database\Eloquent\EloquentManager $manager
 */
// Вернет менеджер подключений Laravel {@see \Illuminate\Database\DatabaseManager}
$laravelDatabase = $manager->getDatabaseManager();

// Получение объекта соединения {@see \Illuminate\Database\Connection}
// если вызвать без параметра, то вернет подключение 'default'
$connection = $manager->getConnection($connectionName ?? null); 

// Добавление подключения
$manager->addConnection(
         [
            'driver' => 'mysql',
            'database' => 'test_db',
            'username' => 'root',
            'password' => 'toor',
            //....
        ],
        'test_connection'
);
// Установка диспетчера событий для моделей {@uses \Illuminate\Contracts\Events\Dispatcher}
$manager->setEventDispatcher($dispatcher);

// Получение диспетчера событий, при неустановленном диспетчере вернет анонимный класс NullDispatcher
$dispatcher = $manager->getEventDispatcher();

// Принудительное переключение подключения моделей во время выполнения
 $manager->withConnection('connection_name', function() {
    $model = new MyModel();
    $model->save([]);
 });

```

### Настройка подключений в зависимости от неймпейсов моделей

Иногда некоторый независимый функционал нужно размещать в отдельной базе данных, 
для этого можно использовать конфигурацию подключений по неймспейсам.

```php
/**
 * @var  \Symbiotic\Database\Eloquent\EloquentManager $manager
 * @var  \Symbiotic\Database\DatabaseManager $symbioticDatabase
 */
// Вернет менеджер подключений Symbiotic {@see \Symbiotic\Database\DatabaseManager}
// Прочтите документацию менеджера и особенности его поведения {@link https://github.com/symbiotic-php/database}
$symbioticDatabase = $manager->getSymbioticDatabaseManager();

// Активен ли поиск подключения по неймспейсу
$bool = $symbioticDatabase->isActiveNamespaceFinder();

// Включение/ выключение поиска по неймспейсам
$symbioticDatabase->activateNamespaceFinder(false);

// Добавление отдельного подключения для модуля
$symbioticDatabase->addNamespaceConnection('\\Modules\\PagesApplication', 'test_connection');

// Получение названия подключения по классу, если выключен вернет null
$pagesConnectionName = $symbioticDatabase->getNamespaceConnection(\Modules\PagesApplication\Models\Event::class); // вернет `test_connection`

// Автоматический поиск подключения по стеку вызова через debug_backtrace, если выключен вернет null
$connectionData = $symbioticDatabase->findNamespaceConnectionName();

```

## Несколько отдельных менеджеров могут работать совместно и перекрестно

```php
// 
/**
* @var \Symbiotic\Database\Eloquent\EloquentManager $manager 
 */
$capsuleOne = new \Symbiotic\Database\Eloquent\EloquentManager($config,\Illuminate\Database\Eloquent\Model::class);
$capsuleTwo = new \Symbiotic\Database\Eloquent\EloquentManager($config,\Symbiotic\Database\Eloquent\SymbioticModel::class);
$capsuleThree = new \Symbiotic\Database\Eloquent\EloquentManager($config,\Illuminate\Database\Eloquent\Model::class);

$capsuleOne->bootEloquent();
// работаем с первым менеджером
$capsuleOne->popEloquent();

$capsuleThree->bootEloquent();
    // активируем сразу два менеджера
    $capsuleOne->bootEloquent();
        $capsuleTwo->bootEloquent();
        // работаем со вторым менеджером
        $capsuleTwo->popEloquent();
    // работаем с первым менеджером
    $capsuleOne->popEloquent();
// работаем с третьим менеджером
$capsuleOne->popEloquent();
// завершили работу менеджеров, если Laravel инициирован установится его менеджер подключений обратно

```

Получение SchemaBuilder для миграций:

```php
/**
* @var \Symbiotic\Database\Eloquent\EloquentManager $manager
 */
 $schema = $manager->getSchemaBuilder();
 
 $schema->create('table_name', static function(\Illuminate\Database\Schema\Blueprint $table) {
        $blueprint->string('name', 255);
 });
  $schema->drop('table_name');

```

Полная документация по использованию моделей и настройки подключений

Laravel [https://laravel.com/docs/10.x/eloquent](https://laravel.com/docs/10.x/eloquent).


