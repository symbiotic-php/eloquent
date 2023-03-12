<?php

declare(strict_types=1);

namespace Symbiotic\Database\Eloquent;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\DatabaseTransactionsManager;
use Illuminate\Database\Schema\Builder;
use Symbiotic\Database\DatabaseManager;


class EloquentManager extends \Illuminate\Database\Capsule\Manager
{

    /**
     * Порядок менеджеров соединений
     *
     * Последний текущий
     *
     * @var \Illuminate\Database\ConnectionResolverInterface[]
     */
    protected static array $resolvers = [];

    /**
     * Порядок менеджеров соединений
     *
     * @var Dispatcher[]
     */
    protected static array $dispatchers = [];

    /**
     * @var array
     */
    protected static array $namespacesConfigs = [];

    /**
     * @param DatabaseManager $symbioticDatabaseManager    Менеджер подключений {@see DatabaseManager::fromArray()}
     * @param string          $baseModelClass              Базовый класс модели для которого будут инициализироваться
     *                                                     подключения
     */
    public function __construct(
        protected DatabaseManager $symbioticDatabaseManager,
        protected string $baseModelClass = SymbioticModel::class
    ) {
        parent::__construct();

        $config = $this->getContainer()['config'];
        $config['database.connections'] = $symbioticDatabaseManager;
        $config['database.default'] = $symbioticDatabaseManager->getDefaultConnectionName();
        /** @see DatabaseManager::__toString() ** */
        /*$config['database.default'] = $symbioticConnectionsManager; Нельзя использовать магию вне Хогвардса!*/
        $this->getContainer()->singleton('db.transactions', function ($app) {
            return new DatabaseTransactionsManager;
        });
    }

    /**
     * Конфигуратор подключений
     *
     * @link https://github.com/symbiotic-php/database
     *
     * @return DatabaseManager
     */
    public function getSymbioticDatabaseManager(): DatabaseManager
    {
        return $this->symbioticDatabaseManager;
    }

    /**
     * Установка менеджера подключений в модели
     *
     * @param string|null $baseModelClass Базовый класс модели от которого вы будете наследоваться
     *
     * @info Важно: Если вы будете использовать свою наследованную базовую модель, вам необходимо также переопределить
     *              статические переменные и подключить trait как тут:{@see SymbioticModel}
     *       Для проведени ная тестов с вашей моделью наследуйтеы
     *       {@see \Symbiotic\Tests\Database\Eloquent\AbstractEloquentTestCase} определите ваш класс модели
     *
     *
     * @return $this
     */
    public function bootEloquent(string $baseModelClass = null): static
    {
        if (null === $baseModelClass) {
            $baseModelClass = $this->baseModelClass;
        }

        if (!method_exists($baseModelClass, 'setConnectionResolver')) {
            throw new \DomainException('BaseClass is not instanceof Laravel model!');
        }

        if (empty(static::$resolvers[$baseModelClass]) && !empty($previousResolver = $baseModelClass::getConnectionResolver())) {
            static::$resolvers[$baseModelClass][] = $previousResolver;
            $previousDispatcher = $baseModelClass::getEventDispatcher();
            static::$dispatchers[$baseModelClass][] = $previousDispatcher ?: $this->getNullDispatcher();
        }

        $baseModelClass::setConnectionResolver(static::$resolvers[$baseModelClass][] = $this->manager);
        $baseModelClass::setEventDispatcher(static::$dispatchers[$baseModelClass][] = $this->getEventDispatcher());
        if (method_exists($baseModelClass, 'setNamespaceConnectionConfig')) {
            $baseModelClass::setNamespaceConnectionConfig(
                static::$namespacesConfigs[$baseModelClass][] = $this->symbioticDatabaseManager
            );
        } else {
            /** @see DatabaseManager::__toString() */
            $this->getContainer()['config']['database.default'] = $this->getSymbioticDatabaseManager();
        }

        return $this;
    }

    /**
     * Извлечение менеджера подключений
     *
     * @return $this
     */
    public function popEloquent(string $baseModelClass = null): void
    {
        if (null === $baseModelClass) {
            $baseModelClass = $this->baseModelClass;
        }

        array_pop(static::$resolvers[$baseModelClass]);
        $previous = end(static::$resolvers[$baseModelClass]);
        $previous ? $baseModelClass::setConnectionResolver($previous) : $baseModelClass::unsetConnectionResolver();


        array_pop(static::$dispatchers[$baseModelClass]);
        $previous = end(static::$dispatchers[$baseModelClass]);
        $previous ? $baseModelClass::setEventDispatcher($previous) : $baseModelClass::unsetEventDispatcher();

        if (method_exists($baseModelClass, 'setNamespaceConnectionConfig')) {
            array_pop(static::$namespacesConfigs[$baseModelClass]);
            $previous = end(static::$namespacesConfigs[$baseModelClass]);
            $baseModelClass::setNamespaceConnectionConfig(!empty($previous) ? $previous : null);
        }
    }

    /**
     * @param string   $connectionName
     * @param callable $closure
     *
     * @return void
     */
    public function withConnection(string $connectionName, callable $closure): void
    {
        $symbioticDB = $this->symbioticDatabaseManager;

        $isActiveNS = $symbioticDB->isActiveNamespaceFinder();
        $symbioticDB->activateNamespaceFinder(false);
        $previous = $this->getDatabaseManager()->getDefaultConnection();
        $this->getDatabaseManager()->setDefaultConnection($connectionName);

        $closure();

        $this->getDatabaseManager()->setDefaultConnection($previous);

        $symbioticDB->activateNamespaceFinder($isActiveNS);
    }

    /**
     * Return container dispatcher or NullDispatcher
     *
     * @return Dispatcher
     */
    public function getEventDispatcher(): Dispatcher
    {
        $dispatcher = parent::getEventDispatcher();

        return $dispatcher ?: $this->getNullDispatcher();
    }

    /**
     * @param string|null $connectionName
     *
     * @return Builder
     */
    public function getSchemaBuilder(string $connectionName = null): Builder
    {
        if (null === $connectionName) {
            $connectionName = $this->symbioticDatabaseManager->findNamespaceConnectionName();
        }
        return $connectionName ?
            $this->getDatabaseManager()->connection($connectionName)->getSchemaBuilder()
            : $this->getDatabaseManager()->getSchemaBuilder();
    }

    /**
     * Количество менеджеров активных
     *
     * @return int
     */
    public static function countActiveResolvers(): int
    {
        $count = 0;
        foreach (static::$resolvers as $v) {
           $count += count($v);
        }
        return $count;
    }

    /**
     * Build the database manager instance.
     *
     * @return void
     */
    protected function setupManager(): void
    {
        // Лень делать декоратор
        $factory = new ConnectionFactory($this->container);
        $this->manager = new ExtendLaravelDatabaseManager($this->container, $factory);
    }

    /**
     * Null Dispatcher
     *
     * @return Dispatcher
     */
    private function getNullDispatcher(): Dispatcher
    {
        return new class implements Dispatcher {

            public function listen($events, $listener = null) {}

            public function push($event, $payload = []) {}

            public function flush($event) {}

            public function forget($event) {}

            public function forgetPushed() {}

            public function subscribe($subscriber) {}

            public function until($event, $payload = [])
            {
                return null;
            }

            public function dispatch($event, $payload = [], $halt = false)
            {
                return null;
            }

            public function hasListeners($eventName)
            {
                return false;
            }
        };
    }
}