<?php

namespace IceCreamRouter;

use Symfony\Component\Routing\Route as SymfonyRoute;

class Route {

    private $method;

    private $name;

    private $callable;

    /**
     * Constructor
     *
     * @param string $name      - name of route, eg: '/foo'
     * @param string $method    - eg: 'GET'
     * @param closure $callable - eg: function($request, $response) {}, can ommit $response if not a GET.
     */
    public function __construct(string $name, string $method, callable $callable) {
        $this->name     = $name;
        $this->method   = strtoupper($method);
        $this->callable = $callable;
    }

    /**
     * Returns a new symfony route.
     *
     * @return Symfony\Component\Routing\Route
     */
    public function getRoute() {
        return new SymfonyRoute(
            $this->name,
            ['callback' => $this->callable],
            [], [], '', [],
            [$this->method]
        );
    }
}
