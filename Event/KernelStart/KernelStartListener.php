<?php

namespace Sruuua\Kernel\Event\KernelStart;

use Sruuua\EventDispatcher\Interfaces\ListenerInterface;
use Sruuua\Logger\Logger;

class KernelStartListener implements ListenerInterface
{
    private Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function listen(): string
    {
        return KernelStartEvent::class;
    }

    public function __invoke(object $event)
    {
        $this->logger->info("Kernel start at {date}", ['date' => $event->getDate()->format('H:i:s-m/d/Y')]);
    }
}
