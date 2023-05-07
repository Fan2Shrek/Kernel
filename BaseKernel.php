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
use Symfony\Component\Dotenv\Dotenv;

abstract class BaseKernel
{
    /**
     * @var Container
     */
    private Container $container;

    /**
     * @var string[]
     */
    private array $env = [];

    private ?EventDispatcher $eventDispatcher = null;

    private ClassLoader $classLoader;

    public function __construct(ClassLoader $classLoader)
    {
        ErrorHandler::initialize();

        $this->classLoader = $classLoader;
        $this->InitializeContainer();
        $this->getEventDispatcher()->dispatch(new KernelStartEvent(new \DateTime()));
    }

    public function handle(Request $request)
    {
        $page = $this->container->get('Sruuua\Routing\RouterBuilder')->getRouter()->getRoute($request->getRequestedPage());

        if (null !== $page) {
            $func = $page->getFunction()->getName();
            $page->getController()->$func(...array_map(
                fn ($opt) => $this->container->get($opt->getName()),
                $page->getOptions()
            ));
        } else {
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
        if (empty($this->env)) {
            $dotenv = new Dotenv();
            $dotenv->load('../.env');

            $this->env = $_ENV;
        }

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
        } else {
            $this->container = (new ContainerBuilder($this, $this->classLoader))->getContainer();
            $cachePool->save(new Cache('container', $this->container));
        }

        $this->container->set('cachePool', $cachePool);

        return $this->container;
    }
}
