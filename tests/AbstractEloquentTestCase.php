<?php

namespace Symbiotic\Tests\Database\Eloquent;


use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use Symbiotic\Database\Eloquent\EloquentManager;
use Symbiotic\Database\Eloquent\SymbioticModel;


abstract class AbstractEloquentTestCase extends BaseTestCase
{


    /**
     * @covers \Symbiotic\Database\Eloquent\EloquentManager::bootEloquent
     * @covers \Symbiotic\Database\Eloquent\EloquentManager::popEloquent
     * @covers \Symbiotic\Database\Eloquent\SymbioticModel::getConnectionResolver
     * @covers \Symbiotic\Database\Eloquent\SymbioticModel::getEventDispatcher
     * @covers \Illuminate\Database\Eloquent\Model::getConnectionResolver
     * @covers \Illuminate\Database\Eloquent\Model::getEventDispatcher
     * @return void
     */
    public function testSingle(): void
    {
        TestEloquentManager::reset();

        $this->assertNull($this->testModelClass::getConnectionResolver());
        $this->assertNull($this->testModelClass::getEventDispatcher());
        $this->assertNull(SymbioticModel::getConnectionResolver());
        $this->assertNull(SymbioticModel::getEventDispatcher());
        $this->assertNull(Model::getConnectionResolver());
        $this->assertNull(Model::getEventDispatcher());

        $capsule = $this->buildEloquent(0);

        $capsule->bootEloquent();
        $this->capsuleTest($capsule, 0);
        $capsule->popEloquent();

        $this->assertNull($this->testModelClass::getConnectionResolver());
        $this->assertNull($this->testModelClass::getEventDispatcher());
        $this->assertNull(SymbioticModel::getConnectionResolver());
        $this->assertNull(SymbioticModel::getEventDispatcher());
        $this->assertNull(Model::getConnectionResolver());
        $this->assertNull(Model::getEventDispatcher());
    }

    /**
     * @param EloquentManager $capsule
     * @param int             $managerNum
     *
     * @return void
     */
    protected function capsuleTest(EloquentManager $capsule, int $managerNum): void
    {
        $this->assertEquals(
            $this->managers[$managerNum]['default'],
            $this->testModelClass::getConnectionResolver()->getDefaultConnection()
        );

        $this->assertEquals($capsule->getDatabaseManager(), $this->testModelClass::getConnectionResolver());
        $this->assertInstanceOf(Dispatcher::class, $this->testModelClass::getEventDispatcher());
        // Если базовая модель другая, то тестируем нашу
        if ($this->testModelClass !== SymbioticModel::class) {
            $this->assertNull(SymbioticModel::getConnectionResolver(), 'SymbioticModel database manager is not Null!');
            $this->assertNull(SymbioticModel::getEventDispatcher(), 'SymbioticModel Event Dispatcher is not Null!');
        }
        if ($this->testModelClass !== Model::class) {
            $this->assertNull(Model::getConnectionResolver(), 'Laravel database manager is not Null!');
            $this->assertNull(Model::getEventDispatcher(), 'Laravel Event Dispatcher is not Null!');
        }
    }

    /**
     * @covers \Symbiotic\Database\Eloquent\EloquentManager::bootEloquent
     * @covers \Symbiotic\Database\Eloquent\EloquentManager::popEloquent
     * @covers \Symbiotic\Database\Eloquent\SymbioticModel::getConnectionResolver
     * @covers \Symbiotic\Database\Eloquent\SymbioticModel::getEventDispatcher
     * @covers \Illuminate\Database\Eloquent\Model::getConnectionResolver
     * @covers \Illuminate\Database\Eloquent\Model::getEventDispatcher
     * @return void
     */
    public function testAllManagers(): void
    {
        $capsule = $this->buildEloquent(0);
        $capsule1 = $this->buildEloquent(1);

        $capsule->bootEloquent();
        $this->capsuleTest($capsule, 0);
        $capsule->popEloquent();


        $capsule->bootEloquent();
        $capsule1->bootEloquent();

        $this->capsuleTest($capsule1, 1);
        $capsule1->popEloquent();
        $this->capsuleTest($capsule, 0);
        $capsule->popEloquent();

        $this->nullModelsAsserts(
            [
                $this->testModelClass,
                SymbioticModel::class,
                Model::class
            ]
        );


        $capsule->bootEloquent();
        $capsule1->bootEloquent();
        $capsule->bootEloquent();
        $this->capsuleTest($capsule, 0);
        $capsule->popEloquent();
        $this->capsuleTest($capsule1, 1);
        $capsule1->popEloquent();
        $this->capsuleTest($capsule, 0);
        $capsule->popEloquent();

        $this->nullModelsAsserts(
            [
                $this->testModelClass,
                SymbioticModel::class,
                Model::class
            ]
        );
    }

    protected function nullModelsAsserts(array $classes): void
    {
        foreach ($classes as $class) {
            $this->assertNull($class::getConnectionResolver());
            $this->assertNull($class::getEventDispatcher());
        }
    }

    /**
     * @covers \Symbiotic\Database\Eloquent\EloquentManager::getSchemaBuilder
     * @return void
     */
    public function testSchema(): void
    {
        $db = $this->buildEloquent(0);
        $this->assertEquals(
            $db->getDatabaseManager()->connection($this->managers[0]['default']),
            $db->getSchemaBuilder()->getConnection()
        );

        $this->assertEquals(
            $db->getDatabaseManager()->connection('mysql_dev'),
            $db->getSchemaBuilder('mysql_dev')->getConnection()
        );
    }


}
