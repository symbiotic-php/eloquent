<?php

namespace Symbiotic\Tests\Database\Eloquent;


use Illuminate\Database\Eloquent\Model;
use Symbiotic\Tests\Database\Eloquent\Models\LaravelPackage\LaravelModel;

class LaravelEloquentTest extends AbstractEloquentTestCase
{
    protected string $testModelClass = Model::class;

    /**
     * @covers \Symbiotic\Database\Eloquent\ExtendLaravelDatabaseManager::getDefaultConnection
     * @return void
     */
    public function testNamespaceConnection(): void
    {
        $manager = $this->buildEloquent(0);
        $manager->bootEloquent();
        $manager->getSymbioticDatabaseManager()
            ->addNamespaceConnection('\\Symbiotic\\Tests\\Database\\Eloquent\\Models\\LaravelPackage', 'app2');

        $laravelModel = new LaravelModel();

        $this->assertSame('app2', $laravelModel->getConnection()->getName());

        $manager->withConnection('mysql', function (){
            $laravelModel = new LaravelModel();

            $this->assertSame('mysql', $laravelModel->getConnection()->getName());
        });
    }
}
