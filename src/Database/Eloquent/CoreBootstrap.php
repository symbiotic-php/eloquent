<?php

declare(strict_types=1);

namespace Symbiotic\Database\Eloquent;

use Symbiotic\Container\DIContainerInterface;
use Symbiotic\Core\AbstractBootstrap;
use Symbiotic\Core\CoreInterface;
use Symbiotic\Database\DatabaseManager;

/**
 * For Symbiotic framework
 */
class CoreBootstrap extends AbstractBootstrap
{
    public function bootstrap(DIContainerInterface $core): void
    {
        /**
         * todo: update in road runner and tests
         *
         */
        $core->singleton(EloquentManager::class, static function (CoreInterface $core) {
            $manager = new EloquentManager($core->get(DatabaseManager::class));
            $manager->bootEloquent();

            return $manager;
        });
        $popEvent = static function (CoreInterface $core) {
            $core->get(EloquentManager::class)->popEloquent();
            if (EloquentManager::countActiveResolvers()) {
                // todo: log error
                // $core->get(LoggerInterface::class)->message('Error');
                // todo: add debug_trace info for bootEloquent method in debug mode  (decorator)
            }
        };
        $core->then($popEvent);
        $core->onComplete($popEvent);
    }
}