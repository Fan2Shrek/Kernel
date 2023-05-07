<?php

namespace Sruuua\Kernel\Event\Route;

use Sruuua\HTTPBasics\Request;

class RouteNotFoundEvent
{
    private Request $request;

    /**
     * @return Request
     */
    public function getResquest(): Request
    {
        return $this->request;
    }

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}
