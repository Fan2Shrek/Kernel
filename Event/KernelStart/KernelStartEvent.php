<?php

namespace Sruuua\Kernel\Event\KernelStart;

class KernelStartEvent
{
    private \DateTime $date;

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function __construct(\DateTime $date)
    {
        $this->date = $date;
    }
}
