<?php

namespace Symbiotic\Tests\Database\Eloquent;


use Symbiotic\Tests\Database\Eloquent\Models\MyModel;

class NamespaceConnectionTest extends BaseTestCase
{

    public function runModelsTest($managerId = 0): void
    {
        $model = new Models\App1\AppModel();
        $this->assertSame(
            $this->managers[$managerId]['namespaces'][__NAMESPACE__ . '\\Models\\App1'],
            $model->getConnectionName()
        );

        $model2 = new Models\App2\AppModel();
        $this->assertSame(
            $this->managers[$managerId]['namespaces'][__NAMESPACE__ . '\\Models\\App2'],
            $model2->getConnectionName()
        );

        $default = new MyModel();
        $this->assertNull(
            $default->getConnectionName(),
            'У модели должно быть не установлено подключение, берется по умолчанию из менеджера.'
        );
    }

    /**
     * @covers \Symbiotic\Database\Eloquent\SymbioticModel::getConnectionName
     * @return void
     */
    public function testAllManagers(): void
    {
        $one = $this->buildEloquent(0);
        $two = $this->buildEloquent(1);
        //.... 3 пойду пиво пить
        $one->bootEloquent();
        $this->runModelsTest(0);
        $one->popEloquent();

        $two->bootEloquent();
        $this->runModelsTest(1);
        $two->popEloquent();
    }

    /**
     * @covers \Symbiotic\Database\Eloquent\EloquentManager::withConnection
     * @covers \Symbiotic\Database\Eloquent\SymbioticModel::getConnection
     * @return void
     */
    public function testWithConnection(): void
    {
        $db = $this->buildEloquent(0);
        $db->bootEloquent();
        $default = $db->getDatabaseManager()->getDefaultConnection();
        $db->withConnection('new_connect', function () use ($db) {
            $model = new Models\App1\AppModel();
            $this->assertEquals(
                $db->getDatabaseManager()->connection('new_connect'),
                $model->getConnection()
            );

            $model2 = new Models\App2\AppModel();
            $this->assertEquals(
                $db->getDatabaseManager()->connection('new_connect'),
                $model2->getConnection()
            );

            $default = new MyModel();
            $this->assertEquals(
                $db->getDatabaseManager()->connection('new_connect'),
                $default->getConnection()
            );
        });
        $this->assertSame($default, $db->getDatabaseManager()->getDefaultConnection());
        $db->popEloquent();
    }

}
