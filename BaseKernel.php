<?php

namespace Sruuua\Kernel;

use Composer\Autoload\ClassLoader;
use Sruuua\Cache\Cache;
use Sruuua\Cache\CacheBuilder;
use Sruuua\DependencyInjection\Container;
use Sruuua\DependencyInjection\ContainerBuilder;
use Sruuua\Error\ErrorHandler;
use Sruuua\EventDispatcher\EventDispatcher;
use Sruuua\HTTPBasics\Request;
use Sruuua\HTTPBasics\Response\Response;
use Sruuua\Kernel\Event\KernelStart\KernelStartEvent;
use Sruuua\Kernel\Event\Route\RouteFindEvent;
use Sruuua\Kernel\Event\Route\RouteNotFoundEvent;
use Symfony\Component\Dotenv\Dotenv;

abstract class BaseKernel
{
    /**
     * @var Container
     */
    protected Container $container;

    /**
     * @var string[]
     */
    protected array $env = [];

    protected ?EventDispatcher $eventDispatcher = null;

    protected ClassLoader $classLoader;

    public function __construct(ClassLoader $classLoader)
    {
        ErrorHandler::initialize();

        $this->classLoader = $classLoader;
        $this->InitializeContainer();
        $this->env = $this->loadEnv();
        $this->getEventDispatcher()->dispatch(new KernelStartEvent(new \DateTime()));
    }

    public function handle(Request $request)
    {
        $page = $this->container->get('Sruuua\Routing\RouterBuilder')->getRouter()->getRoute($request->getRequestedPage());

        $this->container->set('request', $request);

        if (null !== $page) {
            $this->getEventDispatcher()->dispatch(new RouteFindEvent($request, $page));
            $func = $page->getFunction()->getName();
            $page->getController()->$func(...array_map(
                fn ($opt) => $opt instanceof \ReflectionParameter ? $this->container->get($opt->getType()->getName()) : $opt,
                $page->getOptions()
            ));
        } else {
            $this->getEventDispatcher()->dispatch(new RouteNotFoundEvent($request));
            $resp = new Response(404, 'Page was not found :(');
            $resp->response();
        }
    }

    /**
     * Return the env values
     *
     * @return mixed[]
     */
    public function getEnv(): array
    {
        return $this->env;
    }

    public function getEventDispatcher(): EventDispatcher
    {
        if (null === $this->eventDispatcher) {
            $this->eventDispatcher = $this->container->get('Sruuua\EventDispatcher\EventDispatcher');
        }

        return $this->eventDispatcher;
    }

    public function getContainer(): Container
    {
        if (null !== $this->container) return $this->container;

        return $this->initializeContainer();
    }

    public function initializeContainer(): Container
    {
        $cachePool = CacheBuilder::buildFromFiles();

        if ($cachePool->hasItem('container')) {
            $this->container = $cachePool->getItem('container')->get();

            # if container cached was made by application
            $this->container->set('App\Kernel', $this);
        } else {
            $this->container = (new ContainerBuilder($this, $this->classLoader))->getContainer();
            $cachePool->save(new Cache('container', $this->container));
        }

        $this->container->set('cachePool', $cachePool);

        return $this->container;
    }

    public function loadEnv(): array
    {
        $dotenv = new Dotenv();
        $dotenv->load('../.env');

        return $this->env = $_ENV;
    }
}
