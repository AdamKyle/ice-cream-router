<?php

namespace IceCreamRouter;

use Symfony\Component\Routing\Route as SymfonyRoute;

class Route {

    private $method;

    private $name;

    private $action;

    /**
     * Constructor
     *
     * @param string $name      - name of route, eg: '/foo'
     * @param string $method    - eg: 'GET'
     * @param mixed  $action    - eg: function($request, $response) {}, can ommit $response if not a GET.
     */
    public function __construct(string $name, string $method, $action) {
        $this->name     = $name;
        $this->method   = strtoupper($method);
        $this->action = $action;
    }

    public function getAction() {
        return $this->action;
    }

    public function setAction($action) {
        $this->action = $action;
    }

    /**
     * Returns a new symfony route.
     *
     * @return Symfony\Component\Routing\Route
     */
    public function getRoute() {
        return new SymfonyRoute(
            $this->name,
            ['action' => $this->action],
            [], [], '', [],
            [$this->method]
        );
    }
}
