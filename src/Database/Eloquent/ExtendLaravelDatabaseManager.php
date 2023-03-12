<?php

declare(strict_types=1);

namespace Symbiotic\Database\Eloquent;

class ExtendLaravelDatabaseManager extends \Illuminate\Database\DatabaseManager
{
    /**
     * Get the default connection name.
     *
     * @return string
     *
     * @uses \Symbiotic\Database\DatabaseManager::__toString()
     * @link https://github.com/symbiotic-php/database/blob/master/README.RU.md
     *
     * @see  EloquentManager::bootEloquent()
     *
     */
    public function getDefaultConnection(): string
    {
        return (string)$this->app['config']['database.default'];
    }
}