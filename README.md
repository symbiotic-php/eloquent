# Symbiotic (Laravel Eloquent)
README.RU.md  [РУССКОЕ ОПИСАНИЕ](https://github.com/symbiotic-php/eloquent/blob/master/README.RU.md)

**Eloquent wrapper package (`laravel/database`) for parallel independent work with Laravel models of separate applications.**

## Installation

```
composer require symbiotic/eloquent
```

## Description

Configuration conflicts occur when third-party functionality using the `laravel/database` package works together connections.
The current package inherits the model and manager class, which allows you to work in parallel with Laravel to several
independent libraries with their own connection settings and use Laravel models.
It is also possible to configure connections by basic namespaces,
this makes it possible for each packet to specify its own connection.

### Usage

Manager initialization

```php

  $config = [
       'default' => 'my_connect_name',
        // Connections by package namespaces
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
    
  // Building from an array of data {@link https://github.com/symbiotic-php/database}
  $connectionsConfig = \Symbiotic\Database\DatabaseManager::fromArray($config);
  
  // Manager initialization
  $manager = new \Symbiotic\Database\Eloquent\EloquentManager(
        $connectionsConfig,
        \Symbiotic\Database\Eloquent\SymbioticModel::class // Base model class
        // If you are using Laravel models -  \Illuminate\Database\Eloquent\Model::class
        );
            
  // Additionally, you can install the Event Manager (\Illuminate\Contracts\Events\Dispatcher)
  $manager->setEventDispatcher(new Dispatcher());

  // Activating your connection manager for models
  $manager->bootEloquent();
  
  // Your code and requests....
  
  // Extracting the current connection manager and installing the previous one (if any)
  $manager->popEloquent();

```
When you initialize the Laravel base model `\Illuminate\Database\Eloquent\Model::class` you will globally overwrite the manager
connections in all descendants of the model,
so be sure to check out your connection manager after your functionality is finished.
You can use the inherited model `\Symbiotic\Database\Eloquent\SymbioticModel::class` as a base,
it eliminates all configuration conflicts.

It is recommended to inherit from the SymbioticModel class:

```php

use Symbiotic\Database\Eloquent\SymbioticModel;
 
class User extends SymbioticModel
{
    //....
}

```

### Basic Methods

```php
/**
* @var  \Symbiotic\Database\Eloquent\EloquentManager $manager
 */
// Returns the Laravel connection manager {@see \Illuminate\Database\DatabaseManager}
$laravelDatabase = $manager->getDatabaseManager();

// Getting a Connection Object {@see \Illuminate\Database\Connection}
// if called without a parameter, it will return the connection 'default'
$connection = $manager->getConnection($connectionName ?? null); 

// Adding a connection
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
// Installing an Event Dispatcher for Models {@uses \Illuminate\Contracts\Events\Dispatcher}
$manager->setEventDispatcher($dispatcher);

// Getting the event dispatcher, if the dispatcher is not set, will return the anonymous class NullDispatcher
$dispatcher = $manager->getEventDispatcher();

// Force model connection switching at runtime
 $manager->withConnection('connection_name', function() {
    $model = new MyModel();
    $model->save([]);
 });

```

### Configuring connections depending on model namespaces

Sometimes some independent functionality needs to be placed in a separate database,
To do this, you can use the connection configuration by namespaces.

```php
/**
 * @var  \Symbiotic\Database\Eloquent\EloquentManager $manager
 * @var  \Symbiotic\Database\DatabaseManager $symbioticDatabase
 */
// Returns the Symbiotic connection manager {@see \Symbiotic\Database\DatabaseManager}
// Read the documentation of the manager and the features of his behavior {@link https://github.com/symbiotic-php/database}
$symbioticDatabase = $manager->getSymbioticDatabaseManager();

// Is the connection search by namespace active?
$bool = $symbioticDatabase->isActiveNamespaceFinder();

// Enable/disable search by namespaces
$symbioticDatabase->activateNamespaceFinder(false);

// Adding a separate connection for a module
$symbioticDatabase->addNamespaceConnection('\\Modules\\PagesApplication', 'test_connection');

// Getting the name of the connection by class, if disabled, it will return null
$pagesConnectionName = $symbioticDatabase->getNamespaceConnection(\Modules\PagesApplication\Models\Event::class); // return `test_connection`

// Automatic search for a connection along the call stack via debug_backtrace, if disabled, returns null
$connectionData = $symbioticDatabase->findNamespaceConnectionName();

```

## Multiple individual managers can work collaboratively and cross-functionally

```php
/**
* @var \Symbiotic\Database\Eloquent\EloquentManager $manager 
 */
$capsuleOne = new \Symbiotic\Database\Eloquent\EloquentManager($config,\Illuminate\Database\Eloquent\Model::class);
$capsuleTwo = new \Symbiotic\Database\Eloquent\EloquentManager($config,\Symbiotic\Database\Eloquent\SymbioticModel::class);
$capsuleThree = new \Symbiotic\Database\Eloquent\EloquentManager($config,\Illuminate\Database\Eloquent\Model::class);

$capsuleOne->bootEloquent();
// we work with the first manager
$capsuleOne->popEloquent();

$capsuleThree->bootEloquent();
    // activate two managers at once
    $capsuleOne->bootEloquent();
        $capsuleTwo->bootEloquent();
        // working with a second manager
        $capsuleTwo->popEloquent();
    // we work with the first manager
    $capsuleOne->popEloquent();
// working with a third manager
$capsuleOne->popEloquent();
// terminated managers, if Laravel is initiated its connection manager will be installed back

```

Getting SchemaBuilder for migrations:

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

Complete documentation on using models and setting up connections

Laravel [https://laravel.com/docs/10.x/eloquent](https://laravel.com/docs/10.x/eloquent).


