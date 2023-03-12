<?php

declare(strict_types=1);

namespace Symbiotic\Database\Eloquent;

use Symbiotic\Database\NamespaceConnectionsConfigInterface;

trait SymbioticModelTrait
{

    /**
     * Get the current connection name for the model.
     *
     * @return string|null
     */
    public function getConnectionName(): ?string
    {
        return $this->connection ?? static::$namespaceConnectionsConfig->getNamespaceConnection(static::class);
    }

    /**
     * Установка подключений по неймспейсам
     *
     * @param NamespaceConnectionsConfigInterface|null $config
     *
     * @return void
     *
     * @uses     \Symbiotic\Database\DatabaseManager
     * @uses     \Symbiotic\Database\NamespaceConnectionsConfig
     *
     * @used-by  EloquentManager::bootEloquent()
     *
     */
    public static function setNamespaceConnectionConfig(?NamespaceConnectionsConfigInterface $config): void
    {
        static::$namespaceConnectionsConfig = $config;
    }

}