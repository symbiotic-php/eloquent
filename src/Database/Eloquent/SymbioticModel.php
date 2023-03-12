<?php

declare(strict_types=1);

namespace Symbiotic\Database\Eloquent;

use Symbiotic\Database\NamespaceConnectionsConfigInterface;

abstract class SymbioticModel extends \Illuminate\Database\Eloquent\Model
{
    use SymbioticModelTrait;

    /**
     * Конфиг подключений по неймспейсам
     *
     * @var NamespaceConnectionsConfigInterface|null
     */
    protected static ?NamespaceConnectionsConfigInterface $namespaceConnectionsConfig = null;


    /***********************
     * Переопределяем статические переменные, чтобы избежать перезаписи в оригинальной Модели Laravel
     ***********************/
    /**
     * The connection resolver instance.
     *
     * @var \Illuminate\Database\ConnectionResolverInterface
     */
    protected static $resolver;

    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected static $dispatcher;

    /**
     * The array of booted models.
     *
     * @var array
     */
    protected static $booted = [];

    /**
     * The array of trait initializers that will be called on each new instance.
     *
     * @var array
     */
    protected static $traitInitializers = [];

    /**
     * The array of global scopes on the model.
     *
     * @var array
     */
    protected static $globalScopes = [];

    /**
     * The list of models classes that should not be affected with touch.
     *
     * @var array
     */
    protected static $ignoreOnTouch = [];

    /**
     * Indicates whether lazy loading should be restricted on all models.
     *
     * @var bool
     */
    protected static $modelsShouldPreventLazyLoading = false;

    /**
     * The callback that is responsible for handling lazy loading violations.
     *
     * @var callable|null
     */
    protected static $lazyLoadingViolationCallback;

    /**
     * Indicates if an exception should be thrown instead of silently discarding non-fillable attributes.
     *
     * @var bool
     */
    protected static $modelsShouldPreventSilentlyDiscardingAttributes = false;

    /**
     * The callback that is responsible for handling discarded attribute violations.
     *
     * @var callable|null
     */
    protected static $discardedAttributeViolationCallback;

    /**
     * Indicates if an exception should be thrown when trying to access a missing attribute on a retrieved model.
     *
     * @var bool
     */
    protected static $modelsShouldPreventAccessingMissingAttributes = false;

    /**
     * The callback that is responsible for handling missing attribute violations.
     *
     * @var callable|null
     */
    protected static $missingAttributeViolationCallback;

    /**
     * Indicates if broadcasting is currently enabled.
     *
     * @var bool
     */
    protected static $isBroadcasting = true;


}