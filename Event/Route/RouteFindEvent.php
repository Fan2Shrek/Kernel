<?php

namespace Sruuua\Kernel\Event\Route;

use Sruuua\HTTPBasics\Request;
use Sruuua\Routing\Route;

class RouteFindEvent
{
    private Request $request;

    private Route $route;

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return Route
     */
    public function getRoute(): Route
    {
        return $this->route;
    }

    public function __construct(Request $request, Route $route)
    {
        $this->request = $request;
        $this->route = $route;
    }
}
