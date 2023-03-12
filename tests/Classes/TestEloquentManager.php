<?php

namespace Symbiotic\Tests\Database\Eloquent;


use Illuminate\Database\Eloquent\Model;
use Symbiotic\Database\Eloquent\EloquentManager;
use Symbiotic\Database\Eloquent\SymbioticModel;

class TestEloquentManager extends EloquentManager
{

    public static string $testModelClass = SymbioticModel::class;

    public static function reset()
    {
        static::$resolvers = [];
        static::$dispatchers = [];
        static::$namespacesConfigs = [];
        Model::unsetConnectionResolver();
        Model::unsetEventDispatcher();

        SymbioticModel::unsetConnectionResolver();
        SymbioticModel::unsetEventDispatcher();
        SymbioticModel::setNamespaceConnectionConfig(null);

        (static::$testModelClass)::unsetConnectionResolver();
        (static::$testModelClass)::unsetEventDispatcher();
        (static::$testModelClass)::setNamespaceConnectionConfig(null);
    }
}
