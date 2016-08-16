<?php

namespace IceCreamRouter;

use Symfony\Component\Routing\Route as SymfonyRoute;

class Route {

    private $_method;

    private $_name;

    private $_callable;

    /**
     * Constructor
     *
     * @param string $name      - name of route, eg: '/foo'
     * @param string $method    - eg: 'GET'
     * @param closure $callable - eg: function($request, $response) {}, can ommit $response if not a GET.
     */
    public function __construct(string $name, string $method, callable $callable) {
        $this->_name     = $name;
        $this->_method   = strtoupper($method);
        $this->_callable = $callable;
    }

    /**
     * Returns a new symfony route.
     *
     * @return Symfony\Component\Routing\Route
     */
    public function getRoute() {
        return new SymfonyRoute(
            $this->_name,
            ['callback' => $this->_callable],
            [], [], '', [],
            [$this->_method]
        );
    }
}
