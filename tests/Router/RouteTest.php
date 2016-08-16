<?php

use IceCreamRouter\Route;

class RouteTest extends \PHPUnit_Framework_TestCase {

    public function testCreateRoute() {
        $route = new Route('/foo', 'post', function($request){ return $request; });

        $this->assertInstanceOf(Symfony\Component\Routing\Route::class, $route->getRoute());
    }
}
