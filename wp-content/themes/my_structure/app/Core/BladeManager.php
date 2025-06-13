<?php
namespace Core;

use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;

class BladeManager
{
    protected $bladeFactory;

    public function __construct($viewPaths, $cachePath)
    {
        $container = $this->initContainer();

        // Registra il container globale Laravel
        Container::setInstance($container);

        // Fallback per funzione globale app()
        if (! function_exists('app')) {
            function app($abstract = null)
            {
                $container = Container::getInstance();
                return is_null($abstract) ? $container : $container->make($abstract);
            }
        }

        // Dummy Application che implementa l’interfaccia ApplicationContract
        $container->instance(ApplicationContract::class, new class implements ApplicationContract
        {
            public function bound($abstract)
            {return false;}
            public function runningInConsole()
            {return false;}

            public function version()
            {}
            public function basePath($path = '')
            {}
            public function bootstrapPath($path = '')
            {}
            public function configPath($path = '')
            {}
            public function databasePath($path = '')
            {}
            public function environmentPath()
            {}
            public function resourcePath($path = '')
            {}
            public function storagePath()
            {}
            public function environment(...$args)
            {}
            public function isDownForMaintenance()
            {}
            public function registerConfiguredProviders()
            {}
            public function register($provider, $force = false)
            {}
            public function registerDeferredProvider($provider, $service = null)
            {}
            public function resolveProvider($provider)
            {}
            public function boot()
            {}
            public function booting($callback)
            {}
            public function booted($callback)
            {}
            public function bootstrapWith(array $bootstrappers)
            {}
            public function getLocale()
            {}
            public function setLocale($locale)
            {}
            public function isLocale($locale)
            {}
            public function getNamespace()
            {}
            public function hasBeenBootstrapped()
            {}
            public function getProviders($provider = null)
            {return [];}
            public function loadDeferredProvider($provider, $service = null)
            {}
            public function loadDeferredProviders()
            {}
            public function shouldSkipMiddleware()
            {}
            public function setBasePath($basePath)
            {}
            public function path($path = '')
            {}
            public function runningUnitTests()
            {}
            public function terminate()
            {}

            public function alias($abstract, $alias)
            {}
            public function tag($abstracts, $tags)
            {}
            public function tagged($tag)
            {return [];}
            public function bind($abstract, $concrete = null, $shared = false)
            {}
            public function bindIf($abstract, $concrete = null, $shared = false)
            {}
            public function singleton($abstract, $concrete = null)
            {}
            public function singletonIf($abstract, $concrete = null)
            {}
            public function extend($abstract, \Closure $closure)
            {}
            public function instance($abstract, $instance)
            {}
            public function addContextualBinding($concrete, $abstract, $implementation)
            {}
            public function when($concrete)
            {}
            public function factory($abstract)
            {}
            public function flush()
            {}
            public function make($abstract, array $parameters = [])
            {return null;}
            public function call($callback, array $parameters = [], $defaultMethod = null)
            {}
            public function resolved($abstract)
            {return false;}
            public function resolving($abstract, \Closure $callback = null)
            {}
            public function afterResolving($abstract, \Closure $callback = null)
            {}

            public function offsetExists($key)
            {return false;}
            public function offsetGet($key)
            {return null;}
            public function offsetSet($key, $value)
            {}
            public function offsetUnset($key)
            {}

            public function has($id)
            {return false;}
            public function get($id)
            {return null;}

            public function __call($method, $parameters)
            {return null;}
        });

        // Filesystem, Event Dispatcher, Engine Resolver, View Finder
        $container->singleton('files', fn() => new Filesystem);
        $container->singleton('events', fn() => new Dispatcher);
        $container->singleton('view.engine.resolver', function ($container) use ($cachePath) {
            $resolver = new EngineResolver;
            $resolver->register('blade', function () use ($container, $cachePath) {
                $compiler = new BladeCompiler($container['files'], $cachePath);
                return new CompilerEngine($compiler);
            });
            return $resolver;
        });

        if (is_string($viewPaths)) {
            $viewPaths = [$viewPaths];
        }

        $container->singleton('view.finder', fn($container) => new FileViewFinder($container['files'], $viewPaths));

        // Factory di Blade
        $container->singleton('view', function ($container) {
            return new Factory(
                $container['view.engine.resolver'],
                $container['view.finder'],
                $container['events']
            );
        });

        $view = $container->make('view');

        // Registra tutte le possibili interfacce nel container
        $container->alias('view', \Illuminate\Contracts\View\Factory::class);
        $container->alias('view', \Illuminate\View\Factory::class);
        $container->instance('view', $view);
        $container->instance(\Illuminate\Contracts\View\Factory::class, $view);
        $container->instance(\Illuminate\View\Factory::class, $view);

        // Imposta anche il container sulla factory
        $view->setContainer($container);
        $this->registerDirectives($container);
        // Ora assegna a bladeFactory
        $this->bladeFactory = $view;

        // Salva globalmente il container
        $this->setGlobalContainer($container);
    }

    protected function initContainer()
    {
        return new Container;
    }

    protected function registerDirectives($container)
    {
        $compiler = $container['view']->getEngineResolver()->resolve('blade')->getCompiler();
        $compiler->directive('widget', function ($menu_name) {
            return "<?php the_widget('Widget\\MenuWidget', ['menu_name' => {$menu_name}]); ?>";
        });
    }

    public function getBlade()
    {
        return $this->bladeFactory;
    }

    public function render($view, $data = [])
    {
        return $this->bladeFactory->make($view, $data)->render();
    }

    public function setGlobalContainer($container)
    {
        global $globalBladeContainer;
        $globalBladeContainer = $container;
    }
}
