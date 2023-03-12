<?php

namespace Symbiotic\Tests\Database\Eloquent;


use Symbiotic\Database\ConnectionsConfig;
use Symbiotic\Database\DatabaseManager;
use Symbiotic\Database\Eloquent\EloquentManager;
use Symbiotic\Database\Eloquent\SymbioticModel;
use Symbiotic\Database\NamespaceConnectionsConfig;


class BaseTestCase extends \PHPUnit\Framework\TestCase
{

    /**
     * Класс модели над которым будут проводиться тесты
     *
     * @var string
     */
    protected string $testModelClass = SymbioticModel::class;

    protected array $managers = [
        [
            'default' => 'mysql',
            'connections' => [
                'mysql' => [
                    'driver' => 'mysql',
                    'database' => 'database',
                    'username' => 'root',
                    'password' => 'toor',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                ],
                'mysql_dev' => [
                    'driver' => 'mysql',
                    'database' => 'database_dev',
                    'username' => 'root',
                    'password' => 'toor',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                ],
                'app2' => [
                    'driver' => 'mysql',
                    'database' => 'app1',
                    'username' => 'root',
                    'password' => 'toor',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                ],
                'new_connect' => [
                    'driver' => 'mysql',
                    'database' => 'new_connect',
                    'username' => 'root',
                    'password' => 'toor',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                ]
            ],
            'namespaces' => [
                __NAMESPACE__ . '\\NotExists\\' => '',
                __NAMESPACE__ . '\\NotExists\\Two' => '',
                __NAMESPACE__ . '\\Models\\App1' => 'mysql_dev',
                __NAMESPACE__ . '\\Models\\App2' => 'app2'
            ]
        ],
        [
            'default' => 'mysql_1',
            'connections' => [
                'mysql_1' => [
                    'driver' => 'mysql',
                    'database' => 'new_db',
                    'username' => 'user',
                    'password' => 'toor',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                ],
                'm2_app1' => [
                    'driver' => 'mysql',
                    'database' => 'm2_app1',
                    'username' => 'user',
                    'password' => 'toor',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                ],
                'm2_app222' => [
                    'driver' => 'mysql',
                    'database' => 'm2_app222',
                    'username' => 'user',
                    'password' => 'toor',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                ],
            ],
            'namespaces' => [
                __NAMESPACE__ . '\\Models\\App1' => 'm2_app1',
                __NAMESPACE__ . '\\Models\\App2' => 'm2_app222'
            ]
        ]
    ];


    protected function buildEloquent(int $managerNum): EloquentManager
    {
        $connectionsConfig = $this->managers[$managerNum];
        TestEloquentManager::reset();
        return new TestEloquentManager(
         // Symbiotic Database
            new DatabaseManager(
                new ConnectionsConfig($connectionsConfig['connections'], $connectionsConfig['default']),
                new NamespaceConnectionsConfig($connectionsConfig['namespaces'])
            ),
            $this->testModelClass
        );
    }

}
