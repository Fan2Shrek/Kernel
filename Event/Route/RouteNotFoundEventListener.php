<?php

namespace Sruuua\Kernel\Event\Route;

use Sruuua\EventDispatcher\Interfaces\ListenerInterface;
use Sruuua\Logger\Logger;

class RouteNotFoundEventListener implements ListenerInterface
{
    private Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function listen(): string
    {
        return RouteNotFoundEvent::class;
    }

    public function __invoke(object $event)
    {
        $this->logger->info("Route : {routeName} was not found", ['routeName' => $event->getRequest()->getRequestedPage()]);
    }
}
