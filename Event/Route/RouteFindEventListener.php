<?php

namespace Sruuua\Kernel\Event\Route;

use Sruuua\EventDispatcher\Interfaces\ListenerInterface;
use Sruuua\Logger\Logger;

class RouteFindEventListener implements ListenerInterface
{
    private Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function listen(): string
    {
        return RouteFindEvent::class;
    }

    public function __invoke(object $event)
    {
        $this->logger->info("Route : {routeName} was find with {controller}::{function}({args})", [
            'routeName' => $event->getRequest()->getRequestedPage(),
            'controller' => $event->getRoute()->getController()::class,
            'function' => $event->getRoute()->getFunction()->getName(),
            'args' => implode(",", $event->getRoute()->getOptions())
        ]);
    }
}
