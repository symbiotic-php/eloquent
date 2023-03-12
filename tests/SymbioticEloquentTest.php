<?php

namespace Symbiotic\Tests\Database\Eloquent;


use Symbiotic\Tests\Database\Eloquent\Models\App1\Application;
use Symbiotic\Tests\Database\Eloquent\Models\MyModel;

class SymbioticEloquentTest extends AbstractEloquentTestCase
{
    /**
     * @covers \Symbiotic\Database\Eloquent\EloquentManager::withConnection
     * @return void
     */
    public function testWithConnection(): void
    {
        $db = $this->buildEloquent(0);

        $db->bootEloquent();

        $db->withConnection('mysql_dev', function () use ($db) {
            $model = new MyModel();

            $this->assertEquals(
                $db->getDatabaseManager()->connection('mysql_dev'),
                $model->getConnection(),
                'У моделей должно быть тестовое соединение(mysql_dev)!'
            );
        });

        $model = new MyModel();
        $this->assertEquals(
            $db->getDatabaseManager()->connection($this->managers[0]['default']),
            $model->getConnection(),
            'Должно быть соединение по умолчанию("default")!'
        );
    }

    /**
     * @covers \Symbiotic\Database\Eloquent\EloquentManager::getSchemaBuilder
     * @covers \Symbiotic\Database\DatabaseManager::findNamespaceConnectionName
     * @return void
     */
    public function testSchemaBuilderNamespaceConnection(): void {
        $db = $this->buildEloquent(0);
        $this->assertSame($db->getDatabaseManager()->getDefaultConnection(), $db->getSchemaBuilder()->getConnection()->getName());
        $app = new Application($db);
        $this->assertSame($this->managers[0]['namespaces'][  __NAMESPACE__ . '\\Models\\App1'], $app->getSchema()->getConnection()->getName());

    }
}
